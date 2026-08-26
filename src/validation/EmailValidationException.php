<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\validation;

use RuntimeException;

final class EmailValidationException extends RuntimeException
{
    /** @param array<string, list<string>> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('Email validation input is invalid.');
    }

    /** @return array<string, list<string>> */
    public function errors(): array
    {
        return $this->errors;
    }
}
