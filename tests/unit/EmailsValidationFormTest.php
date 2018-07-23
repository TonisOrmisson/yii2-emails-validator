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



    /**
     * Returns a good working LimeSurvey collector
     * @return EmailsValidationForm
     */
    public function baseObject(){
        $model = new EmailsValidationForm();
        return $model;
    }

}