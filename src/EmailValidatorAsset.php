<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use yii\web\AssetBundle;

final class EmailValidatorAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/../resources/ui';
    public $css = ['emails-validator.css'];
    public $js = ['emails-validator.js'];
    public $depends = [];
}
