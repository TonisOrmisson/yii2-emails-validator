<?php

namespace andmemasin\emailsvalidator\controllers;

use andmemasin\emailsvalidator\Module;
use Yii;
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

        return $this->render('index', [
        ]);
    }


}