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
use yii\web\JsonParser;
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

    public function testStringModuleClassBootstrapRegistersItsPrefixedPostApiRoute(): void
    {
        $app = new Application([
            'id' => 'emails-validator-string-module-bootstrap-test',
            'basePath' => dirname(__DIR__, 3),
            'modules' => [
                'emailsvalidator' => Module::class,
            ],
            'components' => [
                'request' => ['cookieValidationKey' => 'emails-validator-string-module-bootstrap-test'],
                'urlManager' => [
                    'class' => UrlManager::class,
                    'enablePrettyUrl' => true,
                    'enableStrictParsing' => true,
                    'showScriptName' => false,
                ],
            ],
        ]);

        (new Bootstrap())->bootstrap($app);

        $app->set('request', Stub::make(Request::class, [
            'getMethod' => 'POST',
            'getPathInfo' => 'emailsvalidator/api/v1/email-validations',
        ]));

        self::assertSame(
            ['emailsvalidator/api/email-validation/index', []],
            $app->urlManager->parseRequest($app->request),
        );
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

    public function testControllerAcceptsAStdClassReturnedByTheJsonParser(): void
    {
        $this->assertControllerAvailable();
        $module = \Yii::$app->getModule('emailsvalidator');
        $module->accessPermissionName = 'configured.email.permission';
        $controller = new EmailValidationController('email-validation', $module);
        $payload = [
            'textInput' => 'good@example.com',
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ];
        $parser = new JsonParser();
        $parser->asArray = false;
        $parsed = $parser->parse(
            json_encode($payload, JSON_THROW_ON_ERROR),
            'application/json',
        );
        self::assertInstanceOf(\stdClass::class, $parsed);

        \Yii::$app->set('request', Stub::make(Request::class, [
            'getIsPost' => true,
            'getMethod' => 'POST',
            'getBodyParams' => $parsed,
        ]));
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $response = $controller->actionIndex();

        self::assertSame(200, $response->statusCode);
        self::assertSame(1, $response->data['meta']['total']);
    }

    public function testRawJsonObjectBodySucceedsWithoutAConfiguredParser(): void
    {
        $controller = $this->controller();
        $response = $this->callRaw($controller, json_encode([
            'textInput' => 'good@example.com',
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ], JSON_THROW_ON_ERROR));

        self::assertSame(200, $response->statusCode);
        self::assertSame(1, $response->data['meta']['total']);
    }

    public function testMalformedJsonReturnsAGenericRequestErrorWithoutAConfiguredParser(): void
    {
        $response = $this->callRaw($this->controller(), '{"textInput":"good@example.com",');

        self::assertSame(422, $response->statusCode);
        self::assertSame([
            'errors' => [
                'request' => ['The request body must be a valid JSON object.'],
            ],
        ], $response->data);
    }

    public function testJsonListAndScalarBodiesReturnAGenericRequestErrorWithoutAConfiguredParser(): void
    {
        foreach ([["good@example.com"], 'true'] as $body) {
            $response = $this->callRaw(
                $this->controller(),
                is_string($body) ? $body : json_encode($body, JSON_THROW_ON_ERROR),
            );

            self::assertSame(422, $response->statusCode);
            self::assertSame([
                'errors' => [
                    'request' => ['The request body must be a valid JSON object.'],
                ],
            ], $response->data);
        }
    }

    public function testNumericKeyJsonObjectKeepsObjectSemanticsWithoutAConfiguredParser(): void
    {
        $response = $this->callRaw($this->controller(), json_encode([
            0 => 'unexpected',
            'textInput' => 'good@example.com',
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ], JSON_THROW_ON_ERROR));

        self::assertSame(422, $response->statusCode);
        self::assertArrayHasKey(0, $response->data['errors']);
        self::assertSame(['Unknown property.'], $response->data['errors'][0]);
        self::assertArrayNotHasKey('request', $response->data['errors']);
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

    public function testRequestPipelineRejectsPostWithInvalidCsrfToken(): void
    {
        $this->configureAccess(true);
        \Yii::$app->set('request', Stub::make(Request::class, [
            'getIsPost' => true,
            'getMethod' => 'POST',
            'getUserIP' => '127.0.0.1',
            'getBodyParams' => [],
            'getCsrfToken' => 'server-token',
            'getCsrfTokenFromHeader' => 'invalid-token',
            'validateCsrfToken' => false,
        ]));
        $controller = $this->controller();

        try {
            $controller->beforeAction(new Action('index', $controller));
            self::fail('A POST with an invalid CSRF token must be rejected.');
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

    public function testMalformedJsonWithMissingCsrfIsRejectedAsGenericCsrfError(): void
    {
        $this->assertMalformedJsonWithCsrfHeaderIsRejected(null);
    }

    public function testMalformedJsonWithInvalidCsrfIsRejectedAsGenericCsrfError(): void
    {
        $this->assertMalformedJsonWithCsrfHeaderIsRejected('invalid-token');
    }

    public function testAuthorizedPostWithParserFailureIsNormalizedByTheRequestPipeline(): void
    {
        $this->configureAccess(true);
        $parserDetails = 'configured parser detail: malformed JSON';
        $parserCalls = 0;
        $rawBody = '{"textInput":"parser-secret",';

        \Yii::$app->set('request', Stub::make(Request::class, [
            'getIsPost' => true,
            'getMethod' => 'POST',
            'getUserIP' => '127.0.0.1',
            'getBodyParams' => static function () use (&$parserCalls, $parserDetails): array {
                ++$parserCalls;
                throw new BadRequestHttpException($parserDetails);
            },
            'getCsrfToken' => 'csrf-token',
            'getCsrfTokenFromHeader' => 'csrf-token',
            'getRawBody' => $rawBody,
            'getContentType' => 'application/json',
        ]));
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $controller = $this->controller();

        $result = null;
        $exception = null;
        try {
            $result = $controller->runAction('index');
        } catch (BadRequestHttpException $caught) {
            $exception = $caught;
        }

        self::assertNull(
            $exception,
            $exception === null
                ? 'The parser was not invoked by the Yii request pipeline.'
                : 'The configured JSON parser exception leaked: ' . $exception->getMessage(),
        );
        self::assertNull($result);
        self::assertSame(1, $parserCalls);
        self::assertSame(422, \Yii::$app->response->statusCode);
        self::assertSame([
            'errors' => [
                'request' => ['The request body must be a valid JSON object.'],
            ],
        ], \Yii::$app->response->data);

        $encoded = json_encode(\Yii::$app->response->data);
        self::assertIsString($encoded);
        self::assertStringNotContainsString($parserDetails, $encoded);
        self::assertStringNotContainsString($rawBody, $encoded);
    }

    private function assertMalformedJsonWithCsrfHeaderIsRejected(?string $csrfHeader): void
    {
        $this->configureAccess(true);
        $parserDetails = 'configured parser detail: malformed JSON';
        $rawBody = '{"textInput":"parser-secret",';
        $serverToken = \Yii::$app->security->maskToken(hash('sha256', 'server-token'));
        $csrfToken = $csrfHeader === null
            ? null
            : \Yii::$app->security->maskToken(hash('sha256', $csrfHeader));

        \Yii::$app->set('request', Stub::construct(Request::class, [], [
            'getIsPost' => true,
            'getMethod' => 'POST',
            'getUserIP' => '127.0.0.1',
            'getBodyParams' => static function () use ($parserDetails): array {
                throw new BadRequestHttpException($parserDetails);
            },
            'getCsrfToken' => $serverToken,
            'getCsrfTokenFromHeader' => $csrfToken,
            'getRawBody' => $rawBody,
            'getContentType' => 'application/json',
        ]));
        $controller = $this->controller();

        $exception = null;
        try {
            $controller->runAction('index');
            self::fail('Malformed JSON with an invalid CSRF token must be rejected.');
        } catch (BadRequestHttpException $caught) {
            $exception = $caught;
        }

        self::assertInstanceOf(BadRequestHttpException::class, $exception);
        self::assertSame('Unable to verify your data submission.', $exception?->getMessage());
        self::assertStringNotContainsString($parserDetails, $exception?->getMessage() ?? '');
        self::assertStringNotContainsString($rawBody, $exception?->getMessage() ?? '');
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

    private function callRaw(EmailValidationController $controller, string $body): Response
    {
        $request = new Request(['parsers' => []]);
        $request->setRawBody($body);
        $server = $_SERVER;
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        try {
            \Yii::$app->set('request', $request);
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $response = $controller->actionIndex();
            self::assertInstanceOf(Response::class, $response);

            return $response;
        } finally {
            $_SERVER = $server;
        }
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
