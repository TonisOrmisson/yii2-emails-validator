<?php
namespace andmemasin\emailsvalidator;

use andmemasin\emailsvalidator\models\EmailsValidationForm;
use andmemasin\myabstract\test\ModelTestTrait;

class EmailsValidationFormTest extends \Codeception\Test\Unit
{
    use ModelTestTrait;

    /**
     * @var \andmemasin\emailsvalidator\UnitTester
     */
    protected $tester;

    /** @var EmailsValidationForm */
    protected $model;

    
    protected function _before()
    {
        $this->model = $this->baseObject();
    }

    protected function _after()
    {
    }

    public function testLoadEmailAddresses() {
        $this->model->textInput = "tonis@andmemasin.eu\rinfo@andmemasin.eu,not-valid@i-do-not-exist.yii";
        $result = $this->invokeMethod($this->model, 'loadEmailAddresses');
        $this->assertEquals(2, count($this->model->emailAddresses));
        $this->assertEquals(1, count($this->model->failingEmailAddresses));
    }

    public function testProcess() {
        $this->assertEquals(true, $this->model->process());
    }



    /**
     * Returns a good working LimeSurvey collector
     * @return EmailsValidationForm
     */
    public function baseObject(){
        $model = new EmailsValidationForm();
        return $model;
    }

}