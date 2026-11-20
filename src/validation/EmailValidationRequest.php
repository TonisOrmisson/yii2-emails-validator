<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\validation;

final readonly class EmailValidationRequest
{
    public function __construct(
        public string $textInput,
        public bool $checkDNS = true,
        public bool $checkSpoof = true,
        public bool $displayOnlyProblems = true,
    ) {
    }

    /** @param array<string, mixed> $payload the v1 request body (snake_case names) */
    public static function fromArray(array $payload): self
    {
        $errors = [];
        $knownFields = ['text_input', 'check_dns', 'check_spoof', 'display_only_problems'];

        if (!array_key_exists('text_input', $payload) || !is_string($payload['text_input']) || $payload['text_input'] === '') {
            $errors['text_input'] = ['The e-mail input is required.'];
        }

        foreach (['check_dns', 'check_spoof', 'display_only_problems'] as $field) {
            if (!array_key_exists($field, $payload)) {
                $errors[$field] = ['This field is required.'];
            } elseif (!is_bool($payload[$field])) {
                $errors[$field] = ['The value must be a boolean.'];
            }
        }

        foreach (array_keys($payload) as $field) {
            if (!in_array($field, $knownFields, true)) {
                $errors[(string) $field] = ['Unknown property.'];
            }
        }

        if ($errors !== []) {
            throw new EmailValidationException($errors);
        }

        return new self(
            $payload['text_input'],
            $payload['check_dns'],
            $payload['check_spoof'],
            $payload['display_only_problems'],
        );
    }
}
