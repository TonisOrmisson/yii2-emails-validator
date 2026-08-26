<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\validation;

final readonly class EmailValidationResult
{
    public function __construct(
        public string $address,
        public bool $needs_trimming,
        public bool $is_valid,
        public bool $is_valid_rfc,
        public bool $is_no_rfc_warnings,
        public bool $is_valid_dns,
        public bool $is_valid_spoof_check,
    ) {
    }
}
