<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\laravel\filament;

use andmemasin\emailsvalidator\Abilities;
use andmemasin\emailsvalidator\laravel\filament\Pages\ValidateEmails;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Assets\Css;

/**
 * The e-mail validator page for a Laravel host's Filament panel: `$panel->plugin(EmailsValidatorPlugin::make())`.
 * Needs `filament/filament` in the host (only suggested here) and `EmailValidationServiceProvider` (API and view).
 */
final class EmailsValidatorPlugin implements Plugin
{
    /** The Yii Module::$accessPermissionName default; the host's API middleware should ask the same item. */
    public const ACCESS_PERMISSION = Abilities::ACCESS;

    /** Where `php artisan filament:assets` publishes the `<emails-validator>` files, the view's assetBase. */
    public const ASSET_BASE = 'js/andmemasin/emailsvalidator';

    /** Navigation group of the page, the Yii sidebar's; the host translates it. */
    public const NAVIGATION_GROUP = 'Tools';

    private function __construct(public readonly string $permission)
    {
    }

    /** @param string $permission RBAC item of the page, as the Yii module's `accessPermissionName` */
    public static function make(string $permission = self::ACCESS_PERMISSION): self
    {
        return new self($permission);
    }

    public static function get(): self
    {
        $plugin = Filament::getCurrentOrDefaultPanel()->getPlugin('emailsvalidator');
        assert($plugin instanceof self);
        return $plugin;
    }

    public function getId(): string
    {
        return 'emailsvalidator';
    }

    public function register(Panel $panel): void
    {
        $resources = dirname(__DIR__, 3) . '/resources/ui';
        // the component and its CSS side by side under one base (the view's assetBase);
        // Css assets only because they accept a public path, none is loaded by Filament itself
        $panel->assets(array_map(
            static fn (string $file): Css => Css::make($file, $resources . '/' . $file)
                ->relativePublicPath(self::ASSET_BASE . '/' . $file)
                ->loadedOnRequest(),
            ['emails-validator.js', 'emails-validator.css'],
        ), 'andmemasin/emailsvalidator');
        $panel->pages([ValidateEmails::class]);
    }

    public function boot(Panel $panel): void
    {
    }
}
