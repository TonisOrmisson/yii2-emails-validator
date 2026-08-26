<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\validation;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\EmailValidation;
use Egulias\EmailValidator\Validation\Extra\SpoofCheckValidation;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Egulias\EmailValidator\Validation\RFCValidation;
use Throwable;

final class EmailValidationService
{
    public function __construct(private readonly int $maxInputLength = 128 * 1024)
    {
        if ($maxInputLength <= 0) {
            throw new \InvalidArgumentException('The maximum input length must be positive.');
        }
    }

    public function validate(EmailValidationRequest $request): EmailValidationReport
    {
        $this->validateInput($request);
        $results = [];

        foreach (preg_split('/\r\n|[\r\n]/', $request->textInput) ?: [] as $address) {
            if ($address === '') {
                continue;
            }
            $results[] = $this->validateAddress($address, $request->checkDNS, $request->checkSpoof);
        }

        return new EmailValidationReport($results);
    }

    private function validateInput(EmailValidationRequest $request): void
    {
        $errors = [];
        if ($request->textInput === '') {
            $errors['textInput'] = ['The e-mail input is required.'];
        } elseif ($this->length($request->textInput) > $this->maxInputLength) {
            $errors['textInput'] = ['The e-mail input is too long.'];
        }
        if ($errors !== []) {
            throw new EmailValidationException($errors);
        }
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }

    private function validateAddress(string $address, bool $checkDNS, bool $checkSpoof): EmailValidationResult
    {
        $validator = new EmailValidator();
        $rfc = $this->run($validator, $address, new RFCValidation());
        $warnings = $this->run($validator, $address, new NoRFCWarningsValidation());
        $dns = $checkDNS
            ? $this->run($validator, $address, new DNSCheckValidation())
            : true;
        $spoof = $checkSpoof
            ? $this->run($validator, $address, new SpoofCheckValidation())
            : true;
        $trimming = $address !== trim($address);

        return new EmailValidationResult(
            $address,
            $trimming,
            !$trimming && $rfc && $warnings && $dns && $spoof,
            $rfc,
            $warnings,
            $dns,
            $spoof,
        );
    }

    private function run(EmailValidator $validator, string $address, EmailValidation $validation): bool
    {
        try {
            return $validator->isValid($address, $validation);
        } catch (Throwable) {
            return false;
        }
    }
}
