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


    /**
     * @return array
     */
    public function badAddressesProvider(){
        return [
            ['my email address'],
            ['my .name@gmail.com'],
            ['my.name @gmail.com'],
            ['my.name@gmail. com'],
            ['my.name@gmail,com'],
            ['my.name@gmail;com'],
            ['my.name@gmail,com'],
            [null],
            [0],
            [1.234],
        ];
    }


    /**
     * @return array
     */
    public function goodAddressesProvider(){
        return [
            ['name@gmail.com'],
            ['my.name@gmail.com'],
            ['mY.nAmE@gmAil.CoM'],
            ['name@amazon.Co.uk'],
        ];
    }


    /**
     * @dataProvider badAddressesProvider
     */
    public function testBadAddressesFail($address) {
        $this->model = new EmailAddress(['address' => $address]);
        $this->assertFalse($this->model->isValid);
    }

    /**
     * @dataProvider goodAddressesProvider
     */
    public function testGoodAddressesDontFail($address) {
        $this->model = new EmailAddress(['address' => $address]);
        $this->assertTrue($this->model->isValid);
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