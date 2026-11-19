<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\laravel\filament\Pages;

use andmemasin\emailsvalidator\laravel\filament\EmailsValidatorPlugin;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

/** The Yii SiteController index: the `<emails-validator>` component on the e-mail validation API. */
final class ValidateEmails extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAtSymbol;

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'validate-emails';

    protected string $view = 'emailsvalidator::filament.validate-emails';

    public static function canAccess(): bool
    {
        return Gate::allows(EmailsValidatorPlugin::get()->permission);
    }

    public static function getNavigationGroup(): string
    {
        return __(EmailsValidatorPlugin::NAVIGATION_GROUP);
    }

    public static function getNavigationLabel(): string
    {
        return __('Validate e-mails');
    }

    public function getTitle(): string
    {
        return __('Bulk e-mail validation');
    }

    /** @return array<string, string> */
    protected function getViewData(): array
    {
        return [
            'apiBase' => '/api/v1/email-validations',
            'csrfToken' => (string) csrf_token(),
            'assetBase' => asset(EmailsValidatorPlugin::ASSET_BASE),
        ];
    }
}
