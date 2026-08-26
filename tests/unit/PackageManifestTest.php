<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use Codeception\Test\Unit;

final class PackageManifestTest extends Unit
{
    public function testComposerManifestDeclaresYiiExtensionAndBootstrap(): void
    {
        $manifest = json_decode(
            (string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        self::assertSame('yii2-extension', $manifest['type']);
        self::assertSame(Bootstrap::class, $manifest['extra']['bootstrap']);
    }
}
