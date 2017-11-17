<?php

namespace andmemasin\emailsvalidator\models;

use andmemasin\emailsvalidator\Module;
use yii\base\Model;
use yii;
use yii\helpers\StringHelper;

class EmailsValidationForm extends Model
{
    //TODO find me a better name!
    /** @var  string */
    public $textInput;

    /** @var  Module */
    public $module;

    /** @var  EmailAddress[] $emailAddresses */
    private $emailAddresses;

    /** @inheritdoc */
    public function init()
    {
        $this->module = \Yii::$app->getModule('emailsvalidator');
        parent::init();
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            [['textInput'], 'required'],
            [['sutextInputbject'], 'string', 'max' => 1024 * 1024 * $this->module->maxInputMB],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'textInput'=>Yii::t('app','E-mail addresses'),
        ];
    }

    /** @inheritdoc */
    public function attributeHints()
    {
        return [
            'textInput'=>Yii::t('app','The addresses can be delimited by line breaks, commas or semi-colons'),
        ];
    }

    public function process(){
        $this->loadEmailAddresses();

        return true;
    }

    private function loadEmailAddresses(){
        if($this->textInput){
            $pattern = '/\r\n|[\r\n]/';
            $array = preg_split( $pattern, $this->textInput );
            if(!empty($array)){
                foreach ($array as $address){
                    $this->emailAddresses[] = new EmailAddress(['address'=>$address]);
                }
            }
        }

    }

}