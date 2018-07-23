<?php
namespace andmemasin\emailsvalidator;

use andmemasin\emailsvalidator\models\EmailAddress;
use andmemasin\myabstract\test\ModelTestTrait;
use Codeception\Stub;

class EmailAddressTest extends \Codeception\Test\Unit
{
    use ModelTestTrait;

    /**
     * @var \andmemasin\emailsvalidator\UnitTester
     */
    protected $tester;

    /** @var EmailAddress */
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
     * @return EmailAddress
     */
    public function baseObject(){
        $model = new EmailAddress();
        return $model;
    }
}