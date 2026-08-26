<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use Codeception\Stub;
use Codeception\Test\Unit;
use andmemasin\emailsvalidator\controllers\api\EmailValidationController;
use yii\web\BadRequestHttpException;
use yii\web\Request;
use yii\web\Response;

final class YiiMalformedJsonParserTest extends Unit
{
    public function testConfiguredJsonParserFailureDoesNotLeakDetails(): void
    {
        $module = \Yii::$app->getModule('emailsvalidator');
        $module->accessPermissionName = 'configured.email.permission';
        $controller = new EmailValidationController('email-validation', $module);
        $rawBody = '{"textInput":"parser-secret",';
        $parserDetails = 'configured parser detail: malformed JSON';

        \Yii::$app->set('request', Stub::make(Request::class, [
            'getIsPost' => true,
            'getMethod' => 'POST',
            'getBodyParams' => static function () use ($parserDetails): array {
                throw new BadRequestHttpException($parserDetails);
            },
            'getRawBody' => $rawBody,
            'getContentType' => 'application/json',
        ]));
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $response = null;
        $exception = null;
        try {
            $response = $controller->actionIndex();
        } catch (BadRequestHttpException $caught) {
            $exception = $caught;
        }

        self::assertInstanceOf(
            Response::class,
            $response,
            $exception === null
                ? 'The controller must return a JSON response.'
                : 'The configured JSON parser exception leaked: ' . $exception->getMessage(),
        );
        self::assertSame(422, $response->statusCode);
        self::assertSame([
            'errors' => [
                'request' => ['The request body must be a valid JSON object.'],
            ],
        ], $response->data);

        $encoded = json_encode($response->data);
        self::assertIsString($encoded);
        self::assertStringNotContainsString($parserDetails, $encoded);
        self::assertStringNotContainsString($rawBody, $encoded);
    }
}
