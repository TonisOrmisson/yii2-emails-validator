<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\api;

use andmemasin\emailsvalidator\validation\EmailValidationException;
use andmemasin\emailsvalidator\validation\EmailValidationReport;
use andmemasin\emailsvalidator\validation\EmailValidationResult;

final class EmailValidationApiResponder
{
    /** The `message` of every 422, as the other modules' APIs answer `{message, errors}`. */
    public const INVALID = 'The given data was invalid.';

    /** @return array{status: int, body: array<string, mixed>} */
    public function success(EmailValidationReport $report, bool $displayOnlyProblems): array
    {
        $results = $displayOnlyProblems ? $report->failing_results : $report->results;

        return [
            'status' => 200,
            'body' => [
                'data' => array_map([$this, 'result'], $results),
                'meta' => [
                    'total' => $report->total,
                    'failed' => $report->failed,
                    'display_only_problems' => $displayOnlyProblems,
                ],
            ],
        ];
    }

    /** @return array{status: int, body: array{message: string, errors: array<string, list<string>>}} */
    public function error(EmailValidationException $exception): array
    {
        return ['status' => 422, 'body' => ['message' => self::INVALID, 'errors' => $exception->errors()]];
    }

    /** @return array<string, bool|string> */
    private function result(EmailValidationResult $result): array
    {
        return [
            'address' => $result->address,
            'needs_trimming' => $result->needs_trimming,
            'is_valid' => $result->is_valid,
            'is_valid_rfc' => $result->is_valid_rfc,
            'is_no_rfc_warnings' => $result->is_no_rfc_warnings,
            'is_valid_dns' => $result->is_valid_dns,
            'is_valid_spoof_check' => $result->is_valid_spoof_check,
        ];
    }
}
