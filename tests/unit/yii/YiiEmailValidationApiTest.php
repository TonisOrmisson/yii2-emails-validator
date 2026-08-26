<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use Codeception\Stub;
use Codeception\Test\Unit;
use andmemasin\emailsvalidator\controllers\api\EmailValidationController;
use yii\base\Action;
use yii\rbac\CheckAccessInterface;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Application;
use yii\web\Request;
use yii\web\Response;
use yii\web\UrlManager;
use yii\web\User;

final class TestApiAccessChecker implements CheckAccessInterface
{
    public function __construct(private readonly bool $allowed)
    {
    }

    public function checkAccess($userId, $permissionName, $params = []): bool
    {
        return $this->allowed;
    }
}

final class TestApiIdentity implements IdentityInterface
{
    public static function findIdentity($id): ?self
    {
        return new self();
    }

    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        return new self();
    }

    public function getId()
    {
        return 1;
    }

    public function getAuthKey()
    {
        return 'test-auth-key';
    }

    public function validateAuthKey($authKey): bool
    {
        return $authKey === 'test-auth-key';
    }
}

final class YiiEmailValidationApiTest extends Unit
{
    public function testModuleExposesOnlyThePostApiRoute(): void
    {
        self::assertTrue(class_exists(Module::class), 'EmailsValidator module is not loadable.');
        self::assertTrue(method_exists(Module::class, 'apiRouteRules'), 'The API route declaration is missing.');

        $routes = Module::apiRouteRules();

        self::assertSame([
            'POST emailsvalidator/api/v1/email-validations' => 'emailsvalidator/api/email-validation/index',
        ], $routes);

        self::assertSame([
            'POST custom-emails/api/v1/email-validations' => 'custom-emails/api/email-validation/index',
        ], Module::apiRouteRules('custom-emails'));
    }

    public function testConfiguredModuleBootstrapRegistersItsPrefixedApiRouteWithoutRootConfig(): void
    {
        $app = new Application([
            'id' => 'emails-validator-bootstrap-test',
            'basePath' => dirname(__DIR__, 3),
            'modules' => [
                'emailsvalidator' => ['class' => Module::class],
            ],
            'components' => [
                'request' => ['cookieValidationKey' => 'emails-validator-bootstrap-test'],
                'urlManager' => [
                    'class' => UrlManager::class,
                    'enablePrettyUrl' => true,
                    'enableStrictParsing' => true,
                    'showScriptName' => false,
                ],
            ],
        ]);

        self::assertTrue(class_exists(Bootstrap::class), 'EmailsValidator Yii bootstrap is missing.');
        (new Bootstrap())->bootstrap($app);

        $app->set('request', Stub::make(Request::class, [
            'getMethod' => 'POST',
            'getPathInfo' => 'emailsvalidator/api/v1/email-validations',
        ]));
        self::assertSame(
            ['emailsvalidator/api/email-validation/index', []],
            $app->urlManager->parseRequest($app->request),
        );

        $app->set('request', Stub::make(Request::class, [
            'getMethod' => 'GET',
            'getPathInfo' => 'emailsvalidator/api/v1/email-validations',
        ]));
        self::assertFalse($app->urlManager->parseRequest($app->request));
    }

    public function testApiControllerUsesConfiguredPermissionPostOnlyAndKeepsCsrfEnabled(): void
    {
        $this->assertControllerAvailable();
        $module = \Yii::$app->getModule('emailsvalidator');
        $module->accessPermissionName = 'configured.email.permission';
        $controller = new EmailValidationController('email-validation', $module);

        self::assertTrue($controller->enableCsrfValidation);
        $behaviors = $controller->behaviors();
        self::assertArrayHasKey('access', $behaviors);
        self::assertArrayHasKey('verbs', $behaviors);
        self::assertSame(['POST'], $behaviors['verbs']['actions']['index']);
        self::assertSame(
            ['configured.email.permission'],
            $behaviors['access']['rules'][0]['roles'],
        );
    }

    public function testValidAndInvalidPostBodiesUseTheSharedJsonContract(): void
    {
        $this->assertControllerAvailable();
        $module = \Yii::$app->getModule('emailsvalidator');
        $module->accessPermissionName = 'configured.email.permission';
        $controller = new EmailValidationController('email-validation', $module);

        $success = $this->call($controller, [
            'textInput' => "good@example.com\nnot-an-email",
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ]);
        self::assertSame(200, $success->statusCode);
        self::assertSame(2, $success->data['meta']['total']);
        self::assertSame(1, $success->data['meta']['failed']);
        self::assertCount(2, $success->data['data']);

        $invalid = $this->call($controller, [
            'textInput' => 123,
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ]);
        self::assertSame(422, $invalid->statusCode);
        self::assertArrayHasKey('textInput', $invalid->data['errors']);
        self::assertStringNotContainsString('Exception', json_encode($invalid->data));
    }

    public function testRequestPipelineRejectsUnauthorizedPost(): void
    {
        $this->configureAccess(false);
        $this->setRequest('POST', true);
        $controller = $this->controller();

        try {
            $controller->beforeAction(new Action('index', $controller));
            self::fail('An unauthorized request must be rejected by the access filter.');
        } catch (ForbiddenHttpException) {
            self::assertTrue(true);
        }
    }

    public function testRequestPipelineRejectsNonPostVerb(): void
    {
        $this->configureAccess(true);
        $this->setRequest('GET', true);
        $controller = $this->controller();

        try {
            $controller->beforeAction(new Action('index', $controller));
            self::fail('A non-POST request must be rejected by the verb filter.');
        } catch (MethodNotAllowedHttpException) {
            self::assertTrue(true);
        }
    }

    public function testRequestPipelineRejectsPostWithoutCsrfToken(): void
    {
        $this->configureAccess(true);
        $this->setRequest('POST', false);
        $controller = $this->controller();
        self::assertTrue($controller->enableCsrfValidation);
        self::assertFalse(\Yii::$app->getRequest()->validateCsrfToken());

        try {
            $controller->beforeAction(new Action('index', $controller));
            self::fail('A POST without a valid CSRF token must be rejected.');
        } catch (BadRequestHttpException) {
            self::assertTrue(true);
        }
    }

    public function testAuthorizedPostPassesThePipelineBeforeReturningJson(): void
    {
        $this->configureAccess(true);
        $this->setRequest('POST', true, [
            'textInput' => 'good@example.com',
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ]);
        $controller = $this->controller();

        self::assertTrue($controller->beforeAction(new Action('index', $controller)));
        $response = $controller->actionIndex();

        self::assertSame(200, $response->statusCode);
        self::assertSame(1, $response->data['meta']['total']);
    }

    private function call(EmailValidationController $controller, array $body): Response
    {
        $request = Stub::make(Request::class, [
            'getIsPost' => true,
            'getMethod' => 'POST',
            'getBodyParams' => $body,
        ]);
        \Yii::$app->set('request', $request);
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $response = $controller->actionIndex();
        self::assertInstanceOf(Response::class, $response);

        return $response;
    }

    private function controller(): EmailValidationController
    {
        $this->assertControllerAvailable();
        $module = \Yii::$app->getModule('emailsvalidator');
        $module->accessPermissionName = 'configured.email.permission';

        return new EmailValidationController('email-validation', $module);
    }

    private function configureAccess(bool $allowed): void
    {
        \Yii::$app->set('user', [
            'class' => User::class,
            'identityClass' => TestApiIdentity::class,
        ]);
        $user = \Yii::$app->getUser();
        $user->setIdentity(new TestApiIdentity());
        $user->accessChecker = new TestApiAccessChecker($allowed);
    }

    private function setRequest(string $method, bool $csrfValid, array $body = []): void
    {
        \Yii::$app->set('request', Stub::make(Request::class, [
            'getIsPost' => $method === 'POST',
            'getMethod' => $method,
            'getUserIP' => '127.0.0.1',
            'getBodyParams' => $body,
            'validateCsrfToken' => $csrfValid,
        ]));
    }

    private function assertControllerAvailable(): void
    {
        self::assertTrue(
            class_exists(EmailValidationController::class),
            'The Yii email-validation API controller is missing.',
        );
        self::assertTrue(method_exists(EmailValidationController::class, 'actionIndex'));
    }
}
