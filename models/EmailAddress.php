<?php

namespace andmemasin\emailsvalidator\models;


use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Egulias\EmailValidator\Validation\SpoofCheckValidation;
use yii\base\Model;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use yii\helpers\StringHelper;


/**
 * Class EmailAddress
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
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

    /** @var boolean */
    public $needsTrimming;

    /** @var string $error */
    public $error;


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
        $this->setNeedsTrimming();
        $this->isValid = ($this->isValidRFC && $this->isNoRFCWarnings && $this->isValidDNS && $this->isValidSpoofCheck );
    }

    private function setNeedsTrimming(){
        $this->needsTrimming = ($this->address !== trim($this->address));
    }

}