<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require_once __DIR__ . '/_support/vendor.php';

$yiiPaths = [
    __DIR__ . '/../vendor/yiisoft/yii2/Yii.php',
    dirname(__DIR__, 4) . '/vendor/yiisoft/yii2/Yii.php',
];

$yiiFile = null;
foreach ($yiiPaths as $candidate) {
    if (is_file($candidate)) {
        $yiiFile = $candidate;
        break;
    }
}

if ($yiiFile === null) {
    throw new RuntimeException('Unable to locate Yii.php for emails validator tests.');
}

require_once($yiiFile);
