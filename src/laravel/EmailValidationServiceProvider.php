<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\laravel;

use andmemasin\emailsvalidator\api\EmailValidationApiResponder;
use andmemasin\emailsvalidator\validation\EmailValidationService;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

final class EmailValidationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EmailValidationService::class, function ($app): EmailValidationService {
            $max = $app['config']->get('emailsvalidator.max_input_kb', 128);
            if (!is_int($max) || $max <= 0) {
                throw new InvalidArgumentException('emailsvalidator.max_input_kb must be a positive integer.');
            }
            return new EmailValidationService($max * 1024);
        });
        $this->app->singleton(EmailValidationApiResponder::class);
    }

    public function boot(): void
    {
        $this->configuredMiddleware('emailsvalidator.middleware');
        $this->configuredMiddleware('emailsvalidator.csrf_middleware');
        $max = $this->app['config']->get('emailsvalidator.max_input_kb', 128);
        if (!is_int($max) || $max <= 0) {
            throw new InvalidArgumentException('emailsvalidator.max_input_kb must be a positive integer.');
        }
        $this->loadViewsFrom(__DIR__ . '/views', 'emailsvalidator');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
    }

    /** @return list<string> */
    private function configuredMiddleware(string $key): array
    {
        $value = $this->app['config']->get($key);
        if (is_string($value) && trim($value) !== '') {
            return [$value];
        }
        if (!is_array($value) || $value === []) {
            throw new InvalidArgumentException($key . ' must contain at least one middleware.');
        }
        foreach ($value as $middleware) {
            if (!is_string($middleware) || trim($middleware) === '') {
                throw new InvalidArgumentException($key . ' must contain non-empty middleware names.');
            }
        }
        return array_values($value);
    }
}
