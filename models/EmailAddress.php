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

    public function init()
    {
        parent::init();
        $this->validator = new EmailValidator();
    }

}