# Changelog

## Unreleased

- API v1 (breaking): the request body names are snake_case as in every other v1 API: `text_input`, `check_dns`, `check_spoof`, `display_only_problems` (were `textInput`, `checkDNS`, `checkSpoof`, `displayOnlyProblems`); a 422 names the fields the same way (`errors.text_input`). `EmailValidationRequest::fromArray()` reads the new names, `EmailValidationService` reports `text_input`; the Yii form keeps its `textInput` field (`SiteController` maps the error). The `<emails-validator>` component sends the new names; OpenAPI updated.
- API v1: a 422 answers `{"message": "The given data was invalid.", "errors": {...}}`, the error shape of the other modules' APIs (was `{"errors"}` only; `errors` is unchanged), on Laravel and Yii (`EmailValidationApiResponder::INVALID`). The OpenAPI file lists the base paths as the other modules do (`servers`: `/api/v1` for Laravel, `/emailsvalidator/api/v1` for Yii; the path is `/email-validations`; URLs are unchanged) and names the operation `emailValidation.store` (was `validateEmailAddresses`).

## 2.3.0 - 2026-11-19

- The ability name is a stable public contract: new `Abilities::ACCESS` (`access::emailsValidator`), never renamed; a host maps its own roles to it through its RBAC or Gate. The Yii `accessPermissionName` default and `EmailsValidatorPlugin::ACCESS_PERMISSION` are this constant; nothing changes in what is checked.

## 2.2.0 - 2026-11-19

- Filament admin: new `laravel\filament\EmailsValidatorPlugin` (`$panel->plugin(EmailsValidatorPlugin::make())`; `filament/filament` is only suggested) with the page `Pages\ValidateEmails` (`/admin/validate-emails`, Tools > Validate e-mails, as the Yii sidebar), which mounts `<emails-validator>` through the `emailsvalidator::index` view on the Laravel e-mail validation API. The page asks the Yii `accessPermissionName` default `access::emailsValidator` (`EmailsValidatorPlugin::make($item)` for another item); the host should put the same item on the API (`emailsvalidator.middleware`, e.g. `can:access::emailsValidator`). `php artisan filament:assets` publishes the component and its CSS to `public/js/andmemasin/emailsvalidator/`.
- `<emails-validator>` draws its own textarea and button borders, which a host's CSS reset (Filament's Tailwind preflight) removed.
