<?php
/**
 * Created by PhpStorm.
 * User: tonis_o
 * Date: 17.11.17
 * Time: 17:39
 */

namespace andmemasin\emailsvalidator\models;


use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Egulias\EmailValidator\Validation\SpoofCheckValidation;
use yii\base\Model;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
/**
 * Class EmailAddress
 * @package andmemasin\emailsvalidator\models
 */
class EmailAddress extends Model
{
    /** @var  EmailValidator $validator */
    private $validator;

    /** @var string $address */
    public $address;

    /** @var boolean */
    public $isValid = false;

    /** @var boolean */
    public $isValidRFC = false;

    /** @var boolean */
    public $isNoRFCWarnings = false;

    /** @var boolean */
    public $isValidDNS = false;

    /** @var boolean */
    public $isValidSpoofCheck = false;

    /** @var string $error */
    public $error;

    /** @var  MultipleValidationWithAnd */
    private $additionalValidationMethods;

    public function init()
    {
        parent::init();
        $this->validator = new EmailValidator();

        $this->runValidator();
    }

    private function runValidator(){
        $this->isValidRFC = $this->validator->isValid($this->address, new RFCValidation());
        $this->isNoRFCWarnings = $this->validator->isValid($this->address, new NoRFCWarningsValidation());
        $this->isValidDNS = $this->validator->isValid($this->address, new DNSCheckValidation());
        $this->isValidSpoofCheck = $this->validator->isValid($this->address, new SpoofCheckValidation());
    }

}