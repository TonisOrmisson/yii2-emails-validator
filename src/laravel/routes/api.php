<?php

declare(strict_types=1);

use andmemasin\emailsvalidator\laravel\Http\EmailValidationController;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\Route;

/** @var Repository $config */
$config = Container::getInstance()->make('config');

Route::post('api/v1/email-validations', EmailValidationController::class)
    ->middleware(array_merge(
        (array) $config->get('emailsvalidator.middleware'),
        (array) $config->get('emailsvalidator.csrf_middleware'),
    ));
