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

}