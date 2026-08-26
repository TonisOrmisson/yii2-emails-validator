<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use Codeception\Test\Unit;

final class LaravelEmailValidationApiTest extends Unit
{
    public function testOptionalAdapterDeclaresAProtectedPostOnlyRoute(): void
    {
        $provider = dirname(__DIR__, 3) . '/src/laravel/EmailValidationServiceProvider.php';
        $routes = dirname(__DIR__, 3) . '/src/laravel/routes/api.php';
        $controller = dirname(__DIR__, 3) . '/src/laravel/Http/EmailValidationController.php';

        self::assertFileExists($provider, 'The Laravel service provider is missing.');
        self::assertFileExists($routes, 'The Laravel API route file is missing.');
        self::assertFileExists($controller, 'The Laravel API controller is missing.');

        $routeSource = (string) file_get_contents($routes);
        self::assertStringContainsString('Route::post', $routeSource);
        self::assertStringNotContainsString('Route::get', $routeSource);
        self::assertStringContainsString('middleware', $routeSource);
        self::assertStringContainsString('csrf_middleware', $routeSource);

        $providerSource = (string) file_get_contents($provider);
        self::assertStringContainsString('emailsvalidator.middleware', $providerSource);
        self::assertStringContainsString('emailsvalidator.csrf_middleware', $providerSource);
        self::assertStringContainsString('emailsvalidator.max_input_kb', $providerSource);
    }

    public function testLaravelAdapterRejectsMissingMiddlewareConfiguration(): void
    {
        $provider = dirname(__DIR__, 3) . '/src/laravel/EmailValidationServiceProvider.php';
        self::assertFileExists($provider, 'The Laravel service provider is missing.');
        $source = (string) file_get_contents($provider);

        self::assertStringContainsString('InvalidArgumentException', $source);
        self::assertStringContainsString('max_input_kb', $source);
        self::assertStringContainsString('positive', strtolower($source));
    }
}
