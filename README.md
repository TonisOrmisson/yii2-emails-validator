# Yii2 e-mails validator

This package provides a stateless bulk e-mail validation module. It uses the
existing Egulias RFC, RFC-warning, DNS, and spoof checks and does not write
email addresses, validation history, or any other data to a database.

## Yii2

Register the module as `emailsvalidator` and configure its existing
`accessPermissionName`, `maxInputKB` (128 by default), and
`displayFlashMessages` settings. The legacy `/emailsvalidator` page, public
`EmailAddress` and `EmailsValidationForm` models, console command, defaults,
and direct POST rendering remain available. GET renders the accessible native
`<emails-validator>` component; POST remains the server-rendered compatibility
path.

Merge `andmemasin\\emailsvalidator\\Module::apiRouteRules()` into the
application URL rules. It registers only the protected POST endpoint:

```
POST /api/v1/email-validations
```

The endpoint accepts `textInput`, `checkDNS`, `checkSpoof`, and
`displayOnlyProblems`, and returns the documented `200` result or `422`
field-error response. The OpenAPI 3.1 document is at
`resources/openapi/email-validation-v1.json`.

## Optional Laravel adapter

Laravel is not a runtime dependency. Install the optional Illuminate packages
when using the adapter, register
`andmemasin\\emailsvalidator\\laravel\\EmailValidationServiceProvider`, and
provide explicit configuration:

```php
'emailsvalidator' => [
    'middleware' => ['auth'],
    'csrf_middleware' => ['web'],
    'max_input_kb' => 128,
],
```

Both middleware lists must be non-empty. `max_input_kb` must be a positive
integer; 128 is used only when the setting is absent. Register or load the
package route file and render the Blade view with non-empty `apiBase`,
`csrfToken`, and `assetBase` values. The host must serve the build-free files
in `resources/ui` (the package has no npm or frontend build step).

The module is stateless and has no one-writer rule: **not applicable: this
module writes nothing**.
