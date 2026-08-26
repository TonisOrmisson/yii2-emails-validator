<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\models;

use andmemasin\emailsvalidator\Module;
use andmemasin\emailsvalidator\validation\EmailValidationRequest;
use yii\base\Model;
use Yii;

class EmailsValidationForm extends Model
{
    public $textInput;
    /** @var Module */
    public $module;
    public $emailAddresses = [];
    public $failingEmailAddresses = [];
    public $checkDNS = true;
    public $checkSpoof = true;
    public $displayOnlyProblems = true;
    public $checkedDomains = [];

    public function init(): void
    {
        if (!$this->module instanceof Module) {
            $this->module = Yii::$app->getModule('emailsvalidator');
        }
        parent::init();
    }

    public function rules(): array
    {
        return [
            [['textInput'], 'required'],
            [['textInput'], 'string', 'max' => 1024 * $this->module->maxInputKB],
            [['checkDNS', 'checkSpoof', 'displayOnlyProblems'], 'boolean'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'textInput' => Yii::t('app', 'E-mail addresses'),
            'checkDNS' => Yii::t('app', 'Perform DNS check'),
            'checkSpoof' => Yii::t('app', 'Perform spoofing check'),
            'displayOnlyProblems' => Yii::t('app', 'Display only e-mails with problems in results'),
        ];
    }

    public function attributeHints(): array
    {
        return [
            'textInput' => Yii::t('app', 'One e-mail address per line'),
            'checkDNS' => Yii::t('app', 'Checking DNS will increase processing time'),
        ];
    }

    public function process(): bool
    {
        $this->loadEmailAddresses();
        return true;
    }

    private function loadEmailAddresses(): void
    {
        $this->emailAddresses = [];
        $this->failingEmailAddresses = [];
        if (!$this->textInput) {
            return;
        }

        $report = $this->module->getValidationService()->validate(new EmailValidationRequest(
            $this->textInput,
            (bool) $this->checkDNS,
            (bool) $this->checkSpoof,
            (bool) $this->displayOnlyProblems,
        ));

        foreach ($report->results as $result) {
            if ($result->address === '0') {
                continue;
            }

            $model = EmailAddress::fromValidationResult(
                $result,
                (bool) $this->checkDNS,
                (bool) $this->checkSpoof,
            );
            $this->emailAddresses[] = $model;
            if (!$model->isValid) {
                $this->failingEmailAddresses[] = $model;
            }
        }
    }
}
