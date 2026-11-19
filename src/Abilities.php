<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

/**
 * The ability name (Yii RBAC item, Laravel gate) the e-mails validator checks: a stable public contract, never renamed.
 * A host maps its own roles to it (Yii RBAC children, a Laravel `Gate::define()` or `Gate::before()`); the Yii
 * `Module::$accessPermissionName` and `laravel\filament\EmailsValidatorPlugin::make()` default to it.
 */
final class Abilities
{
    /** The Validate e-mails page and its API. */
    public const ACCESS = 'access::emailsValidator';
}
