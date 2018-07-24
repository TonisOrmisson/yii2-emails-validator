<?php
namespace andmemasin\emailsvalidator;

use andmemasin\emailsvalidator\controllers\SiteController;
use yii\base\Action;

class SiteControllerTest extends \Codeception\Test\Unit
{
    /**
     * @var \andmemasin\emailsvalidator\UnitTester
     */
    protected $tester;

    /** @var SiteController */
    public $model;

    protected function _before()
    {
        $_SERVER['REQUEST_URI']='index.php';
        $config = require( __DIR__ . "/../_config/test.php");
        $this->model = new SiteController('site', new \yii\web\Application($config));
        \Yii::$app->controller = $this->model;
        \Yii::$app->controller->action = new Action('fake', $this->model);
    }

    protected function _after()
    {
    }

    // tests
    public function testActionIndex()
    {
        $result = $this->model->actionIndex();

    }
}