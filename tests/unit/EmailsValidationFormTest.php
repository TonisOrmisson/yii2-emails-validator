<?php
namespace andmemasin\emailsvalidator;

use andmemasin\emailsvalidator\models\EmailsValidationForm;

class EmailsValidationFormTest extends \Codeception\Test\Unit
{
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

    // tests
    public function testSomeFeature()
    {

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