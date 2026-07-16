<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    dirname(__DIR__, 4) . '/vendor/autoload.php',
];

$autoloadFile = null;
foreach ($autoloadPaths as $candidate) {
    if (is_file($candidate)) {
        $autoloadFile = $candidate;
        break;
    }
}

if ($autoloadFile === null) {
    throw new RuntimeException('Unable to locate Composer autoload.php for emails validator tests.');
}

require_once($autoloadFile);

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
