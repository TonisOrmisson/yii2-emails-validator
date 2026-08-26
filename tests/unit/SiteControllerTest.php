<?php
namespace andmemasin\emailsvalidator;

use andmemasin\emailsvalidator\controllers\SiteController;
use andmemasin\emailsvalidator\models\EmailAddress;
use andmemasin\emailsvalidator\models\EmailsValidationForm;
use andmemasin\emailsvalidator\validation\EmailValidationException;
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

        $this->assertIsString($result);
        $this->assertStringContainsString('id="bulk-email-validation"', $result);
        $this->assertStringContainsString('id="email-validation-results"', $result);
        $this->assertStringContainsString('tonis@andmemasin.eu', $result);
        $this->assertStringContainsString('not-valid@i-do-not-exist.yii', $result);
    }

    public function testOverLimitPostStaysOnTheLegacyPageWithAValidationError(): void
    {
        $request = $this->mockRequest([
            'textInput' => str_repeat('a', 128 * 1024 + 1),
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ]);
        \Yii::$app->set('request', $request);

        $result = null;
        try {
            $result = $this->model->actionIndex();
        } catch (EmailValidationException) {
            // The legacy action must convert the shared validation error into a page error.
        }

        $this->assertIsString($result, 'An over-limit legacy submission must stay on the page.');
        $this->assertStringContainsString('id="bulk-email-validation"', $result);
        $this->assertStringContainsString('emailsvalidationform-textinput', $result);
        $this->assertStringContainsString('has-error', $result);
        $this->assertStringNotContainsString('<emails-validator', $result);
    }

    public function testBehaviors() {
        $this->arrayHasKey('access', $this->model->behaviors());
    }

    public function testLegacyResultsEncodeUntrustedAddressesBeforeHighlightingSpaces(): void
    {
        $email = new EmailAddress([
            'address' => ' <script>alert(1)</script> @example.com ',
            'checkDNS' => false,
            'checkSpoof' => false,
        ]);
        $valid = new EmailAddress([
            'address' => 'good@example.com',
            'checkDNS' => false,
            'checkSpoof' => false,
        ]);
        $form = new EmailsValidationForm();
        $form->emailAddresses = [$email, $valid];
        $form->checkDNS = false;
        $form->checkSpoof = false;
        $view = \Yii::$app->getView();
        $output = $view->renderFile(
            dirname(__DIR__, 2) . '/src/views/site/_validation-list.php',
            [
                'model' => $form,
                'dataProvider' => new ArrayDataProvider(['allModels' => [$email, $valid]]),
            ],
        );

        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
        $this->assertStringContainsString('bg-primary', $output);
        $this->assertStringContainsString('danger text-danger', $output);
        $this->assertStringContainsString('text-success', $output);
        $this->assertStringContainsString('warning text-warning', $output);
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
            'getIsPost' => true,
            'getMethod' => 'POST',
            'getBodyParams' => [
                'EmailsValidationForm' => $data
            ],
        ]);
    }

}
