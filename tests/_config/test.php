<?php


$config = [
    'id' => 'test-app',
    'basePath' => dirname(__DIR__). "/../src/",
    'aliases' =>[
        '@vendor' => '@app/../vendor',
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'emailsvalidator' => [
            'class' => 'andmemasin\emailsvalidator\Module',
        ],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'i-am-test',
        ],
    ],
];



return $config;
