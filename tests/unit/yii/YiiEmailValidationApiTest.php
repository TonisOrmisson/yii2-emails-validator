<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use Codeception\Stub;
use Codeception\Test\Unit;
use andmemasin\emailsvalidator\controllers\api\EmailValidationController;
use yii\web\Request;
use yii\web\Response;

final class YiiEmailValidationApiTest extends Unit
{
    public function testModuleExposesOnlyThePostApiRoute(): void
    {
        self::assertTrue(class_exists(Module::class), 'EmailsValidator module is not loadable.');
        self::assertTrue(method_exists(Module::class, 'apiRouteRules'), 'The API route declaration is missing.');

        $routes = Module::apiRouteRules();

        self::assertCount(1, $routes);
        self::assertArrayHasKey('POST api/v1/email-validations', $routes);
        self::assertStringContainsString('email-validation', (string) $routes['POST api/v1/email-validations']);
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

    private function assertControllerAvailable(): void
    {
        self::assertTrue(
            class_exists(EmailValidationController::class),
            'The Yii email-validation API controller is missing.',
        );
        self::assertTrue(method_exists(EmailValidationController::class, 'actionIndex'));
    }
}
