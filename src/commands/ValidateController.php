<?php

namespace  andmemasin\emailsvalidator\commands;

use andmemasin\emailsvalidator\models\EmailAddress;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class ValidateController extends Controller
{

    public function actionIndex($email)
    {
        $model = new EmailAddress(['address' => $email]);
        $model->address = $email;
        $model->validate();

        if ($model->isValid) {
            \Yii::$app->controller->stdout("Address {$email} is valid" . PHP_EOL, Console::FG_GREEN, Console::BOLD);
            return ExitCode::OK;
        }

        \Yii::$app->controller->stdout("Address {$email} is not valid!" . PHP_EOL, Console::FG_RED, Console::BOLD);
        return ExitCode::UNSPECIFIED_ERROR;

    }
}
