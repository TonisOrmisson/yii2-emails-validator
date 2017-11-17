<?php
/**
 * Created by PhpStorm.
 * User: tonis_o
 * Date: 17.11.17
 * Time: 17:39
 */

namespace andmemasin\emailsvalidator\models;


use Egulias\EmailValidator\EmailValidator;
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


    /** @var string $error */
    public $error;

    /** @var  MultipleValidationWithAnd */
    private $additionalValidationMethods;

    public function init()
    {
        parent::init();
        $this->validator = new EmailValidator();
        $this->additionalValidationMethods = new MultipleValidationWithAnd([
            new RFCValidation(),
            new DNSCheckValidation()
        ]);

        $this->runValidator();
    }

    private function runValidator(){
        $this->isValid = $this->validator->isValid($this->address);
        if($this->validator->getError()){
            $this->error = $this->validator->getError()->getMessage();
        }

    }

}