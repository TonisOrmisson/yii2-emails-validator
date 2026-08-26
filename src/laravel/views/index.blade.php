@php
    foreach (['apiBase', 'csrfToken', 'assetBase'] as $required) {
        if (!isset($$required) || !is_string($$required) || trim($$required) === '') {
            throw new \InvalidArgumentException("emailsvalidator view requires a non-empty {$required}.");
        }
    }
@endphp
<link rel="stylesheet" href="{{ rtrim($assetBase, '/') }}/emails-validator.css">
<emails-validator
    api-base="{{ $apiBase }}"
    csrf-token="{{ $csrfToken }}"
    asset-base="{{ $assetBase }}"
></emails-validator>
<script src="{{ rtrim($assetBase, '/') }}/emails-validator.js" defer></script>
