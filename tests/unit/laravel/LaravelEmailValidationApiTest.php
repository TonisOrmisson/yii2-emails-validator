<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use Codeception\Stub;
use Codeception\Test\Unit;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use andmemasin\emailsvalidator\api\EmailValidationApiResponder;
use andmemasin\emailsvalidator\laravel\EmailValidationServiceProvider;
use andmemasin\emailsvalidator\controllers\api\EmailValidationController as YiiEmailValidationController;
use andmemasin\emailsvalidator\laravel\Http\EmailValidationController;
use andmemasin\emailsvalidator\validation\EmailValidationService;
use yii\web\Application as YiiApplication;
use yii\web\Request as YiiRequest;

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

    public function testProviderRegistersOnlyPostRouteWithConfiguredMiddleware(): void
    {
        [, $router] = $this->bootProvider([
            'emailsvalidator' => [
                'middleware' => ['auth:admin'],
                'csrf_middleware' => ['web.csrf'],
                'max_input_kb' => 1,
            ],
        ]);

        $routes = $router->getRoutes()->getRoutes();
        self::assertCount(1, $routes);
        $route = $routes[0];
        self::assertSame(['POST'], $route->methods());
        self::assertSame('api/v1/email-validations', $route->uri());
        self::assertSame(['auth:admin', 'web.csrf'], $route->middleware());
        self::assertSame(EmailValidationController::class, $route->getActionName());
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

    public function testProviderRejectsMissingMiddlewareConfigurationAtBoot(): void
    {
        $container = $this->containerWithConfig(['emailsvalidator' => ['csrf_middleware' => ['web.csrf']]]);
        $provider = new EmailValidationServiceProvider($container);
        $provider->register();

        $this->expectException(InvalidArgumentException::class);
        $provider->boot();
    }

    public function testProviderRejectsNonPositiveConfiguredMaximum(): void
    {
        $container = $this->containerWithConfig([
            'emailsvalidator' => [
                'middleware' => ['auth:admin'],
                'csrf_middleware' => ['web.csrf'],
                'max_input_kb' => 0,
            ],
        ]);
        $provider = new EmailValidationServiceProvider($container);
        $provider->register();

        $this->expectException(InvalidArgumentException::class);
        $container->make(EmailValidationService::class);
    }

    public function testControllerUsesJsonBodyBeforeConflictingQueryInput(): void
    {
        $payload = [
            'textInput' => 'good@example.com',
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ];

        $withoutQuery = $this->callController($payload);
        $withQuery = $this->callController($payload, '?textInput=not-an-email');

        self::assertSame(200, $withoutQuery->getStatusCode());
        self::assertSame($withoutQuery->getData(true), $withQuery->getData(true));
    }

    public function testControllerReturnsMalformedAndOverLimitInputAs422WithoutDetails(): void
    {
        $malformed = $this->callController([
            'textInput' => 123,
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ]);
        self::assertSame(422, $malformed->getStatusCode());
        self::assertSame(['textInput'], array_keys($malformed->getData(true)['errors']));
        self::assertStringNotContainsString('Exception', $malformed->getContent());

        $overLimit = $this->callController([
            'textInput' => str_repeat('a', 128 * 1024 + 1),
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ]);
        self::assertSame(422, $overLimit->getStatusCode());
        self::assertArrayHasKey('textInput', $overLimit->getData(true)['errors']);
    }

    public function testControllerReturnsGenericRequestErrorForMalformedRawJson(): void
    {
        $response = $this->callRawController('{"textInput":"good@example.com",');

        self::assertSame(422, $response->getStatusCode());
        self::assertSame([
            'errors' => [
                'request' => ['The request body must be a valid JSON object.'],
            ],
        ], $response->getData(true));
    }

    public function testControllerReturnsGenericRequestErrorForAJsonArrayBody(): void
    {
        $response = $this->callRawController('["good@example.com"]');

        self::assertSame(422, $response->getStatusCode());
        self::assertSame([
            'errors' => [
                'request' => ['The request body must be a valid JSON object.'],
            ],
        ], $response->getData(true));
    }

    public function testLaravelResponseMatchesTheYiiResponseForTheSamePayload(): void
    {
        $payload = [
            'textInput' => "good@example.com\nnot-an-email",
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ];
        $laravel = $this->callController($payload);

        $application = new YiiApplication(require dirname(__DIR__, 2) . '/_config/test.php');
        $module = $application->getModule('emailsvalidator');
        $yii = new YiiEmailValidationController('email-validation', $module);
        $application->set('request', Stub::make(YiiRequest::class, ['getBodyParams' => $payload]));
        $application->response->format = \yii\web\Response::FORMAT_JSON;
        $yiiResponse = $yii->actionIndex();

        self::assertSame($yiiResponse->statusCode, $laravel->getStatusCode());
        self::assertSame($yiiResponse->data, $laravel->getData(true));
    }

    public function testControllerResponseFilteringAndFieldsMatchTheJsonContract(): void
    {
        $payload = [
            'textInput' => "good@example.com\nnot-an-email",
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
        ];
        $all = $this->callController($payload)->getData(true);
        $problems = $this->callController(array_replace($payload, ['displayOnlyProblems' => true]))->getData(true);

        self::assertSame(['data', 'meta'], array_keys($all));
        self::assertSame(2, $all['meta']['total']);
        self::assertSame(1, $all['meta']['failed']);
        self::assertCount(2, $all['data']);
        self::assertSame(
            ['address', 'needs_trimming', 'is_valid', 'is_valid_rfc', 'is_no_rfc_warnings', 'is_valid_dns', 'is_valid_spoof_check'],
            array_keys($all['data'][0]),
        );
        self::assertSame(1, count($problems['data']));
        self::assertSame(true, $problems['meta']['display_only_problems']);
    }

    /** @return array{0: Container, 1: Router} */
    private function bootProvider(array $config): array
    {
        $container = $this->containerWithConfig($config);
        $router = new Router(new Dispatcher($container), $container);
        $container->instance('router', $router);
        Route::clearResolvedInstance('router');
        Route::setFacadeApplication($container);

        $provider = new EmailValidationServiceProvider($container);
        $provider->register();
        $provider->boot();

        return [$container, $router];
    }

    private function containerWithConfig(array $config): Container
    {
        $container = new Container();
        Container::setInstance($container);
        $container->instance('config', new class ($config) implements \ArrayAccess {
            public function __construct(private readonly array $config)
            {
            }

            public function get(string $key, mixed $default = null): mixed
            {
                $value = $this->config;
                foreach (explode('.', $key) as $part) {
                    if (!is_array($value) || !array_key_exists($part, $value)) {
                        return $default;
                    }
                    $value = $value[$part];
                }
                return $value;
            }

            public function offsetExists(mixed $offset): bool
            {
                return $this->get((string) $offset) !== null;
            }

            public function offsetGet(mixed $offset): mixed
            {
                return $this->get((string) $offset);
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                throw new \LogicException('Test configuration is immutable.');
            }

            public function offsetUnset(mixed $offset): void
            {
                throw new \LogicException('Test configuration is immutable.');
            }
        });

        return $container;
    }

    private function callController(array $payload, string $query = ''): JsonResponse
    {
        return $this->callRawController(
            json_encode($payload, JSON_THROW_ON_ERROR),
            $query,
        );
    }

    private function callRawController(string $body, string $query = ''): JsonResponse
    {
        $request = Request::create(
            '/api/v1/email-validations' . $query,
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            $body,
        );

        return (new EmailValidationController(
            new EmailValidationService(),
            new EmailValidationApiResponder(),
        ))($request);
    }
}
