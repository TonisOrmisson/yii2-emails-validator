<?php
namespace andmemasin\emailsvalidator;

use andmemasin\emailsvalidator\controllers\SiteController;
use Codeception\Stub;
use yii\base\Action;
use yii\web\Request;
use yii\web\View;

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
        $this->assertInternalType('string', $result);
    }

    public function testActionIndexPost() {

        $data = [
            'textInput' => "tonis@andmemasin.eu\rinfo@andmemasin.eu,not-valid@i-do-not-exist.yii",
        ];


        $request = $this->mockRequest($data);
        \Yii::$app->set('request', $request);

        $result = $this->model->actionIndex();
    }

    public function testBehaviors() {
        $this->arrayHasKey('access', $this->model->behaviors());
    }

    /**
     * @param $data
     * @return Request
     */
    private function  mockRequest($data){
        // mock a request
        $_SERVER['REQUEST_URI'] = 'http://localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        \Yii::$app->requestedAction = new Action('site/index', $this->model);
        \Yii::$app->setHomeUrl('http://localhost');
        return Stub::make(Request::class, [
            'getUserIP' =>'127.0.0.1',
            'enableCookieValidation' => false,
            'getUserAgent' => 'Dummy User Agent',
            'getBodyParams' => [
                'EmailsValidationForm' => $data
            ],
        ]);
    }

}