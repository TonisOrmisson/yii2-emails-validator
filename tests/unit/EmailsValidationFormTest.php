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

    public function testProcessSkipsZeroLineWithoutLegacyError(): void
    {
        $this->model->textInput = "good@example.com\n0";
        $this->model->checkDNS = false;
        $this->model->checkSpoof = false;

        $this->assertTrue($this->model->process());
        $this->assertCount(1, $this->model->emailAddresses);
        $this->assertSame('good@example.com', $this->model->emailAddresses[0]->address);
    }

    public function testProcessWithChecksDisabledPreservesInputAndFailureSubset(): void
    {
        $this->model->textInput = "good@example.com\r\n\r\n bad@example.com\nnot-an-email";
        $this->model->checkDNS = false;
        $this->model->checkSpoof = false;
        $this->model->displayOnlyProblems = false;

        $this->assertTrue($this->model->process());
        $this->assertSame(3, count($this->model->emailAddresses));
        $this->assertSame(2, count($this->model->failingEmailAddresses));
        $this->assertSame(' bad@example.com', $this->model->emailAddresses[1]->address);
        $this->assertTrue($this->model->emailAddresses[1]->needsTrimming);
        $this->assertFalse($this->model->emailAddresses[1]->isValid);
        $this->assertTrue($this->model->displayOnlyProblems === false);
    }

    public function testValidationRulesKeepConfiguredMaximumAndBooleanFields(): void
    {
        $rules = $this->model->rules();
        $ruleText = json_encode($rules);

        $this->assertStringContainsString((string) (128 * 1024), $ruleText);
        $this->assertStringContainsString('checkDNS', $ruleText);
        $this->assertStringContainsString('checkSpoof', $ruleText);
        $this->assertStringContainsString('displayOnlyProblems', $ruleText);
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