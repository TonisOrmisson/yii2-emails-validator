<?php

namespace andmemasin\emailsvalidator\controllers;

use andmemasin\emailsvalidator\models\EmailsValidationForm;
use andmemasin\emailsvalidator\Module;
use Yii;
use yii\data\ArrayDataProvider;
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
                'allModels'=>$model->emailAddresses,
                'pagination' => [
                    'pageSize' => count($model->emailAddresses),
                ],
            ]);
        }
        echo count($model->emailAddresses);
        return $this->render('index', [
            'model'=>$model,
            'dataProvider'=>$dataProvider,
        ]);
    }


}
