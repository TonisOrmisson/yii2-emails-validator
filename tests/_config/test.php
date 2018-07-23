<?php


$config = [
    'id' => 'test-app',
    'basePath' => dirname(__DIR__). "/../src/",
    'modules' => [
        'emailsvalidator' => [
            'class' => 'andmemasin\emailsvalidator\Module',
        ],
    ],
];



return $config;
