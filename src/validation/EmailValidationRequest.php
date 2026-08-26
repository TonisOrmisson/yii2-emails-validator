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

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $errors = [];
        $knownFields = ['textInput', 'checkDNS', 'checkSpoof', 'displayOnlyProblems'];

        if (!array_key_exists('textInput', $payload) || !is_string($payload['textInput']) || $payload['textInput'] === '') {
            $errors['textInput'] = ['The e-mail input is required.'];
        }

        foreach (['checkDNS', 'checkSpoof', 'displayOnlyProblems'] as $field) {
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
            $payload['textInput'],
            $payload['checkDNS'],
            $payload['checkSpoof'],
            $payload['displayOnlyProblems'],
        );
    }
}
