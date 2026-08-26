<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\models;

use andmemasin\emailsvalidator\validation\EmailValidationRequest;
use andmemasin\emailsvalidator\validation\EmailValidationService;
use Yii;
use yii\base\Model;

/**
 * Compatibility Yii model for one validated address.
 */
class EmailAddress extends Model
{
    public $address;
    public $isValid = true;
    public $isValidRFC = true;
    public $isNoRFCWarnings = true;
    public $isValidDNS = true;
    public $isValidSpoofCheck = true;
    public $needsTrimming;
    public $error;
    public $checkDNS = true;
    public $checkSpoof = true;

    public function init(): void
    {
        if (!is_string($this->address) || $this->address === '') {
            throw new \ErrorException('need an address here!');
        }
        parent::init();

        $result = (new EmailValidationService())->validate(new EmailValidationRequest(
            $this->address,
            (bool) $this->checkDNS,
            (bool) $this->checkSpoof,
            false,
        ))->results[0];

        $this->isValid = $result->is_valid;
        $this->isValidRFC = $result->is_valid_rfc;
        $this->isNoRFCWarnings = $result->is_no_rfc_warnings;
        $this->isValidDNS = $result->is_valid_dns;
        $this->isValidSpoofCheck = $result->is_valid_spoof_check;
        $this->needsTrimming = $result->needs_trimming;
    }

    public function attributeLabels(): array
    {
        return [
            'address' => Yii::t('app', 'E-mail address'),
            'isValid' => Yii::t('app', 'Is valid?'),
            'isValidRFC' => Yii::t('app', 'In line with RFC standard?'),
            'isNoRFCWarnings' => Yii::t('app', 'No RFC warnings?'),
            'isValidDNS' => Yii::t('app', 'DNS check passed?'),
            'needsTrimming' => Yii::t('app', 'Has spaces to be trimmed?'),
            'isValidSpoofCheck' => Yii::t('app', 'Spoof check OK?'),
        ];
    }
}
