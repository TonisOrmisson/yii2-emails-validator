<?php

namespace andmemasin\emailsvalidator\models;

use andmemasin\emailsvalidator\Module;
use yii\base\Model;
use yii;

class EmailsValidationForm extends Model
{
    //TODO find me a better name!
    /** @var  string */
    public $textInput;

    /** @var  Module */
    public $module;

    /** @var  EmailAddress[] $emailAddresses */
    public $emailAddresses;

    /** @var  boolean */
    public $checkDNS = true;

    /** @var  boolean */
    public $checkSpoof = true;

    public function init()
    {
        $this->module = \Yii::$app->getModule('emailsvalidator');
        parent::init();
    }

    public function rules()
    {
        return [
            [['textInput'], 'required'],
            [['textInput'], 'string', 'max' => 1024 * $this->module->maxInputKB],
            [['checkDNS','checkSpoof'],'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'textInput'=>Yii::t('app','E-mail addresses'),
            'checkDNS'=>Yii::t('app','Perform DNS check'),
            'checkSpoof'=>Yii::t('app','Perform spoofing check'),
        ];
    }

    public function attributeHints()
    {
        return [
            'textInput'=>Yii::t('app','One e-mail address per line'),
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
                    if($address){
                        $model = new EmailAddress([
                            'address'=>$address,
                            'checkDNS'=>$this->checkDNS,
                            'checkSpoof'=>$this->checkSpoof,
                        ]);

                        $this->emailAddresses[] = $model;

                    }
                }
            }
        }
    }



}