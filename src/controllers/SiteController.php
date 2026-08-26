<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\controllers;

use andmemasin\emailsvalidator\Module;
use andmemasin\emailsvalidator\models\EmailsValidationForm;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\i18n\Formatter;
use yii\web\Controller;

class SiteController extends Controller
{
    /** @var Module */
    public $module;

    public function init(): void
    {
        $this->module = Yii::$app->getModule('emailsvalidator');
        parent::init();
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'actions' => ['index'],
                    'allow' => true,
                    'roles' => [$this->module->accessPermissionName],
                ]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['index' => ['GET', 'POST']],
            ],
        ];
    }

    public function actionIndex(): mixed
    {
        $model = new EmailsValidationForm();
        $dataProvider = null;

        if ($model->load(Yii::$app->request->post()) && $model->process()) {
            $dataProvider = new ArrayDataProvider([
                'allModels' => $model->displayOnlyProblems
                    ? $model->failingEmailAddresses
                    : $model->emailAddresses,
                'pagination' => ['pageSize' => count($model->emailAddresses)],
            ]);
            $formatter = new Formatter();

            if ($model->failingEmailAddresses !== []) {
                Yii::$app->session->addFlash('danger', Yii::t('app',
                    'There were {count} e-mail addresses that failed validation!',
                    ['count' => count($model->failingEmailAddresses)],
                ));
            }
            Yii::$app->session->addFlash('success', Yii::t('app',
                'Checked {count} e-mails in {duration}!',
                [
                    'count' => count($model->emailAddresses),
                    'duration' => $formatter->asDuration((int) Yii::getLogger()->getElapsedTime()),
                ],
            ));
        }

        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }
}
