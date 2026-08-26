<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use Codeception\Test\Unit;
use yii\web\Application;
use yii\web\Request;
use yii\web\UrlManager;

final class ModuleTest extends Unit
{
    public function testApiBasePathUsesThePublicRouteForPrettyUrls(): void
    {
        $application = $this->application(true, false);
        (new Bootstrap())->bootstrap($application);

        self::assertSame(
            '/emailsvalidator/api/v1/email-validations',
            $application->getModule('emailsvalidator')->apiBasePath(),
        );
    }

    public function testApiBasePathUsesTheScriptUrlAndInternalRouteForNonPrettyUrls(): void
    {
        $application = $this->application(false, true, '/app.php');
        $url = $application->getModule('emailsvalidator')->apiBasePath();
        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        self::assertSame('/app.php', parse_url($url, PHP_URL_PATH));
        self::assertSame('emailsvalidator/api/email-validation/index', $query['r'] ?? null);
    }

    public function testApiBasePathProducesUsableUrlsAcrossUrlManagerModes(): void
    {
        $cases = [
            [true, true, '/admin/app.php', '', '/admin/app.php/emailsvalidator/api/v1/email-validations'],
            [true, false, '/admin/app.php', '', '/emailsvalidator/api/v1/email-validations'],
            [true, true, '/admin/app.php', '.html', '/admin/app.php/emailsvalidator/api/v1/email-validations.html'],
            [true, false, '/admin/app.php', '.html', '/emailsvalidator/api/v1/email-validations.html'],
            [false, true, '/admin/app.php', '', '/admin/app.php'],
            [false, false, '/admin/app.php', '', '/admin/app.php'],
            [false, true, '/admin/app.php', '.html', '/admin/app.php'],
            [false, false, '/admin/app.php', '.html', '/admin/app.php'],
        ];

        foreach ($cases as [$pretty, $showScriptName, $scriptUrl, $suffix, $expectedPath]) {
            $application = $this->application($pretty, $showScriptName, $scriptUrl, $suffix);
            $url = $application->getModule('emailsvalidator')->apiBasePath();

            self::assertSame($expectedPath, parse_url($url, PHP_URL_PATH));
            if ($pretty) {
                self::assertNull(parse_url($url, PHP_URL_QUERY));
                continue;
            }

            $query = [];
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            self::assertSame('emailsvalidator/api/email-validation/index', $query['r'] ?? null);
        }
    }

    private function application(
        bool $enablePrettyUrl,
        bool $showScriptName,
        string $scriptUrl = '/index.php',
        string $suffix = '',
    ): Application
    {
        $application = new Application([
            'id' => 'emails-validator-module-api-base-test',
            'basePath' => dirname(__DIR__, 2),
            'modules' => [
                'emailsvalidator' => ['class' => Module::class],
            ],
            'components' => [
                'request' => ['cookieValidationKey' => 'emails-validator-module-api-base-test'],
                'urlManager' => [
                    'class' => UrlManager::class,
                    'enablePrettyUrl' => $enablePrettyUrl,
                    'showScriptName' => $showScriptName,
                    'suffix' => $suffix,
                ],
            ],
        ]);
        $request = $application->getRequest();
        self::assertInstanceOf(Request::class, $request);
        $request->setScriptUrl($scriptUrl);
        $request->setBaseUrl('');

        return $application;
    }
}
