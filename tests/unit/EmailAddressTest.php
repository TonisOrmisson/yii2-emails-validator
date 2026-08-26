<?php
namespace andmemasin\emailsvalidator;

use andmemasin\emailsvalidator\models\EmailAddress;
use andmemasin\myabstract\test\ModelTestTrait;
use Codeception\Stub;
use Yii;

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
    public function invalidTypeAddressesProvider(){
        return [
            [null]
            [0],
            [1.234],
        ];
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
     * @dataProvider invalidTypeAddressesProvider
     */
    public function tesInvalidTypeAddressesThrow($address)
    {
        $this->expectException(\ErrorException::class);
        $this->model = new EmailAddress(['address' => $address]);
    }

    /**
     * @dataProvider goodAddressesProvider
     */
    public function testGoodAddressesDontFail($address) {
        $this->model = new EmailAddress(['address' => $address]);
        $this->assertTrue($this->model->isValid);
    }

    public function testCompatibilityPropertiesPreserveSpacesAndDisabledChecks(): void
    {
        $this->model = new EmailAddress([
            'address' => ' good@example.com ',
            'checkDNS' => false,
            'checkSpoof' => false,
        ]);

        $this->assertSame(' good@example.com ', $this->model->address);
        $this->assertTrue($this->model->needsTrimming);
        $this->assertFalse($this->model->isValid);
        $this->assertFalse($this->model->isValidRFC);
        $this->assertFalse($this->model->isNoRFCWarnings);
        $this->assertTrue($this->model->isValidDNS);
        $this->assertTrue($this->model->isValidSpoofCheck);
        $this->assertSame(Yii::t('app', 'E-mail address'), $this->model->attributeLabels()['address']);
    }

    public function testEmptyAddressStillRaisesCompatibilityException(): void
    {
        $this->expectException(\ErrorException::class);
        new EmailAddress(['address' => '']);
    }

    public function testZeroAddressStillRaisesCompatibilityException(): void
    {
        $this->expectException(\ErrorException::class);
        new EmailAddress(['address' => '0']);
    }



    /**
     * Returns a good working LimeSurvey collector
     * @return EmailAddress
     */
    public function baseObject(){
        $model = new EmailAddress(['address'=>'example@example.com']);
        return $model;
    }
}