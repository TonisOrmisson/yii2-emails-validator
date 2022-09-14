<?php

namespace andmemasin\emailsvalidator\models;


use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Egulias\EmailValidator\Validation\Extra\SpoofCheckValidation;
use yii\base\Model;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Yii;

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
    public $isValid = true;

    /** @var boolean */
    public $isValidRFC = true;

    /** @var boolean */
    public $isNoRFCWarnings = true;

    /** @var boolean */
    public $isValidDNS = true;

    /** @var boolean */
    public $isValidSpoofCheck = true;

    /** @var boolean */
    public $needsTrimming;

    /** @var string $error */
    public $error;

    /** @var  boolean */
    public $checkDNS = true;

    /** @var  boolean */
    public $checkSpoof = true;

    public function init()
    {
        parent::init();
        $this->validator = new EmailValidator();

        $this->runValidator();
    }

    public function attributeLabels()
    {
        return [
            'address'=>Yii::t('app','E-mail address'),
            'isValid'=>Yii::t('app','Is valid?'),
            'isValidRFC'=>Yii::t('app','In line with RFC standard?'),
            'isNoRFCWarnings'=>Yii::t('app','No RFC warnings?'),
            'isValidDNS'=>Yii::t('app','DNS check passed?'),
            'needsTrimming'=>Yii::t('app','Has spaces to be trimmed?'),
            'isValidSpoofCheck'=>Yii::t('app','Spoof check OK?'),

        ];
    }


    /**
     * run the different validators
     * @return void
     */
    private function runValidator(){
        $this->isValidRFC = $this->validator->isValid($this->address, new RFCValidation());
        $this->isNoRFCWarnings = $this->validator->isValid($this->address, new NoRFCWarningsValidation());

        if($this->checkDNS){
            $this->isValidDNS = $this->validator->isValid($this->address, new DNSCheckValidation());
        }

        if($this->checkSpoof) {
            $this->isValidSpoofCheck = $this->validator->isValid($this->address, new SpoofCheckValidation());
        }

        $this->setNeedsTrimming();
        $this->isValid = ($this->isValidRFC && $this->isNoRFCWarnings && $this->isValidDNS && $this->isValidSpoofCheck );
    }

    private function setNeedsTrimming(){
        $this->needsTrimming = ($this->address !== trim($this->address));
    }

}