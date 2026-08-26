<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use Codeception\Stub;
use Codeception\Test\Unit;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use InvalidArgumentException;
use yii\web\Request;

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

    public function testYiiGetViewRendersEncodedConfiguredAttributes(): void
    {
        $request = Stub::make(Request::class, [
            'getIsPost' => false,
            'getMethod' => 'GET',
            'getCsrfToken' => '"><script>alert(1)</script>',
        ]);
        $application = \Yii::$app;
        $originalRequest = $application->get('request');
        $application->set('request', $request);

        try {
            $output = $application->getView()->renderFile(
                dirname(__DIR__, 3) . '/src/views/site/index.php',
                [],
            );
        } finally {
            $application->set('request', $originalRequest);
        }

        self::assertStringContainsString('<emails-validator', $output);
        self::assertStringContainsString('&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;', $output);
        self::assertStringNotContainsString('<script>alert(1)</script>', $output);
    }

    public function testBladeViewEscapesConfiguredValuesWhenRendered(): void
    {
        $output = $this->renderBlade([
            'apiBase' => 'https://example.test/?q="><script>alert(1)</script>',
            'csrfToken' => 'csrf"><script>alert(2)</script>',
            'assetBase' => 'https://cdn.example.test/assets',
        ]);

        self::assertStringContainsString('<emails-validator', $output);
        self::assertStringContainsString('&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;', $output);
        self::assertStringContainsString('csrf-token="csrf&quot;&gt;&lt;script&gt;alert(2)&lt;/script&gt;"', $output);
        self::assertStringNotContainsString('<script>', $output);
    }

    public function testBladeViewRequiresEveryConfiguredValue(): void
    {
        foreach (['apiBase', 'csrfToken', 'assetBase'] as $missing) {
            $values = [
                'apiBase' => '/api/v1/email-validations',
                'csrfToken' => 'csrf-token',
                'assetBase' => '/assets/emails-validator',
            ];
            unset($values[$missing]);

            try {
                $this->renderBlade($values);
                self::fail("Blade rendering should reject missing {$missing}.");
            } catch (InvalidArgumentException $exception) {
                self::assertStringContainsString($missing, $exception->getMessage());
            }
        }
    }

    public function testBladeViewRejectsEmptyConfiguredValues(): void
    {
        foreach (['apiBase', 'csrfToken', 'assetBase'] as $empty) {
            $values = [
                'apiBase' => '/api/v1/email-validations',
                'csrfToken' => 'csrf-token',
                'assetBase' => '/assets/emails-validator',
            ];
            $values[$empty] = ' ';

            try {
                $this->renderBlade($values);
                self::fail("Blade rendering should reject an empty {$empty}.");
            } catch (InvalidArgumentException $exception) {
                self::assertStringContainsString($empty, $exception->getMessage());
            }
        }
    }

    public function testComponentSourceDirectlyCoversRequiredControlsAndSafeResultRendering(): void
    {
        $javascript = (string) file_get_contents(dirname(__DIR__, 3) . '/resources/ui/emails-validator.js');

        foreach ([
            "document.createElement('form')",
            "document.createElement('textarea')",
            "input.type = 'checkbox'",
            "submit.type = 'submit'",
            "cell.setAttribute('scope', 'col')",
            'message.textContent = Object.values(errors)',
            'cell.textContent = typeof value',
        ] as $contract) {
            self::assertStringContainsString($contract, $javascript);
        }
    }

    /** @param array<string, string> $values */
    private function renderBlade(array $values): string
    {
        $compiler = new BladeCompiler(new Filesystem(), sys_get_temp_dir());
        $source = (string) file_get_contents(dirname(__DIR__, 3) . '/src/laravel/views/index.blade.php');
        $compiled = $compiler->compileString($source);
        extract($values, EXTR_SKIP);

        ob_start();
        try {
            eval('?>' . $compiled);
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }

        return (string) ob_get_clean();
    }
}
