<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\controllers\api;

use andmemasin\emailsvalidator\Module;
use andmemasin\emailsvalidator\api\EmailValidationApiResponder;
use andmemasin\emailsvalidator\validation\EmailValidationException;
use andmemasin\emailsvalidator\validation\EmailValidationRequest;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

final class EmailValidationController extends Controller
{
    public $enableCsrfValidation = true;

    public function behaviors(): array
    {
        /** @var Module $module */
        $module = $this->module;

        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'actions' => ['index'],
                    'allow' => true,
                    'roles' => [$module->accessPermissionName],
                ]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['index' => ['POST']],
            ],
        ];
    }

    public function actionIndex(): Response
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $responder = new EmailValidationApiResponder();

        try {
            $body = Yii::$app->request->getBodyParams();
            $request = EmailValidationRequest::fromArray(is_array($body) ? $body : []);
            $result = $this->module->getValidationService()->validate($request);
            $payload = $responder->success($result, $request->displayOnlyProblems);
        } catch (EmailValidationException $exception) {
            $payload = $responder->error($exception);
        }

        $response->statusCode = $payload['status'];
        $response->data = $payload['body'];
        return $response;
    }
}
