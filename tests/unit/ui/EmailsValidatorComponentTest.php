<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use Codeception\Test\Unit;

final class EmailsValidatorComponentTest extends Unit
{
    public function testBuildFreeComponentIsAccessibleAndUsesSafeTextRendering(): void
    {
        $javascriptPath = dirname(__DIR__, 3) . '/resources/ui/emails-validator.js';
        $stylesheetPath = dirname(__DIR__, 3) . '/resources/ui/emails-validator.css';

        self::assertFileExists($javascriptPath, 'The EmailsValidator component script is missing.');
        self::assertFileExists($stylesheetPath, 'The EmailsValidator component stylesheet is missing.');

        $javascript = (string) file_get_contents($javascriptPath);
        self::assertStringContainsString('customElements.define', $javascript);
        self::assertStringContainsString('emails-validator', $javascript);
        self::assertStringContainsString("method: 'POST'", $javascript);
        self::assertStringContainsString('csrf', strtolower($javascript));
        self::assertStringContainsString('textContent', $javascript);
        self::assertStringContainsString('aria-live', $javascript);
        self::assertStringNotContainsString('innerHTML', $javascript);
        self::assertStringNotContainsString('insertAdjacentHTML', $javascript);
        self::assertStringNotContainsString('import ', $javascript);
        self::assertStringNotContainsString('npm', strtolower($javascript));

        $stylesheet = (string) file_get_contents($stylesheetPath);
        self::assertStringContainsString(':focus-visible', $stylesheet);
        self::assertStringContainsString('color', $stylesheet);
    }

    public function testYiiAndLaravelViewsMountTheSameConfiguredComponentSafely(): void
    {
        $yiiView = dirname(__DIR__, 3) . '/src/views/site/index.php';
        $bladeView = dirname(__DIR__, 3) . '/src/laravel/views/index.blade.php';

        self::assertFileExists($yiiView, 'The Yii component view is missing.');
        self::assertFileExists($bladeView, 'The Laravel component view is missing.');

        $yiiSource = (string) file_get_contents($yiiView);
        self::assertStringContainsString('<emails-validator', $yiiSource);
        self::assertStringContainsString('api-base', $yiiSource);
        self::assertStringContainsString('csrf-token', $yiiSource);
        self::assertStringContainsString('asset', strtolower($yiiSource));
        self::assertStringContainsString('Html::encode', $yiiSource);

        $bladeSource = (string) file_get_contents($bladeView);
        self::assertStringContainsString('<emails-validator', $bladeSource);
        self::assertStringContainsString('api-base', $bladeSource);
        self::assertStringContainsString('csrf-token', $bladeSource);
        self::assertStringContainsString('asset-base', $bladeSource);
        self::assertStringContainsString('{{', $bladeSource);
        self::assertStringContainsString('apiBase', $bladeSource);
        self::assertStringContainsString('csrfToken', $bladeSource);
        self::assertStringContainsString('assetBase', $bladeSource);
    }
}
