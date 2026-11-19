# Changelog

## 2.2.0 - 2026-11-19

- Filament admin: new `laravel\filament\EmailsValidatorPlugin` (`$panel->plugin(EmailsValidatorPlugin::make())`; `filament/filament` is only suggested) with the page `Pages\ValidateEmails` (`/admin/validate-emails`, Tools > Validate e-mails, as the Yii sidebar), which mounts `<emails-validator>` through the `emailsvalidator::index` view on the Laravel e-mail validation API. The page asks the Yii `accessPermissionName` default `access::emailsValidator` (`EmailsValidatorPlugin::make($item)` for another item); the host should put the same item on the API (`emailsvalidator.middleware`, e.g. `can:access::emailsValidator`). `php artisan filament:assets` publishes the component and its CSS to `public/js/andmemasin/emailsvalidator/`.
- `<emails-validator>` draws its own textarea and button borders, which a host's CSS reset (Filament's Tailwind preflight) removed.
