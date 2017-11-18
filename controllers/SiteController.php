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
                'class' => AccessControl::className(),
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

        $session = Yii::$app->session;
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
            Yii::$app->session->addFlash('success',Yii::t('app','Checked {count} e-mails in {duration}!',[
                'count'=>count($model->emailAddresses),
                'duration'=>$formatter->asDuration(Yii::getLogger()->getElapsedTime())]));
        }
        return $this->render('index', [
            'model'=>$model,
            'dataProvider'=>$dataProvider,
        ]);
    }


}
