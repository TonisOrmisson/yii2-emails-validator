<?php

namespace andmemasin\emailsvalidator\controllers;

use andmemasin\emailsvalidator\models\EmailsValidationForm;
use andmemasin\emailsvalidator\Module;
use Yii;
use yii\data\ArrayDataProvider;
use yii\i18n\Formatter;
use yii\web\Controller;
use yii\filters\AccessControl;

class SiteController extends Controller
{
    /** @var  Module */
    public $module;

    public function init()
    {
        $this->module = \Yii::$app->getModule('emailsvalidator');
        parent::init();
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => [$this->module->accessPermissionName],
                    ],
                ],
            ],
        
        ];
    }

    /**
     * @return mixed
     */
    public function actionIndex()
    {

        /** @var EmailsValidationForm $model */
        $model = new EmailsValidationForm();
        $dataProvider = null;

        if($model->load(Yii::$app->request->post()) && $model->process()){
            $dataProvider = new ArrayDataProvider([
                'allModels'=>($model->displayOnlyProblems ? $model->failingEmailAddresses:$model->emailAddresses),
                'pagination' => [
                    'pageSize' => count($model->emailAddresses),
                ],
            ]);
            $formatter = new Formatter();

            if(!empty($model->failingEmailAddresses)){
                Yii::$app->session->addFlash('danger',Yii::t('app','There were {count} e-mail addresses that failed validation!',[
                    'count'=>count($model->failingEmailAddresses)
                ]));
            }
            Yii::$app->session->addFlash('success',Yii::t('app','Checked {count} e-mails in {duration}!',[
                'count'=>count($model->emailAddresses),
                'duration'=>$formatter->asDuration(Yii::getLogger()->getElapsedTime())
            ]));
        }
        return $this->render('index', [
            'model'=>$model,
            'dataProvider'=>$dataProvider,
        ]);
    }


}
