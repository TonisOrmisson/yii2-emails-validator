<?php
namespace andmemasin\emailsvalidator;

use andmemasin\emailsvalidator\controllers\SiteController;
use andmemasin\emailsvalidator\models\EmailAddress;
use andmemasin\emailsvalidator\models\EmailsValidationForm;
use Codeception\Stub;
use yii\base\Action;
use yii\data\ArrayDataProvider;
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
        $this->assertIsString($result);
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

    public function testLegacyResultsEncodeUntrustedAddressesBeforeHighlightingSpaces(): void
    {
        $email = new EmailAddress([
            'address' => '<script>alert(1)</script> @example.com',
            'checkDNS' => false,
            'checkSpoof' => false,
        ]);
        $form = new EmailsValidationForm();
        $form->emailAddresses = [$email];
        $form->checkDNS = false;
        $form->checkSpoof = false;
        $view = \Yii::$app->getView();
        $output = $view->renderFile(
            dirname(__DIR__, 2) . '/src/views/site/_validation-list.php',
            [
                'model' => $form,
                'dataProvider' => new ArrayDataProvider(['allModels' => [$email]]),
            ],
        );

        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
        $this->assertStringContainsString('bg-primary', $output);
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
