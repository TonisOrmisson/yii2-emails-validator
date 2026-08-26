<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\validation;

final readonly class EmailValidationReport
{
    /** @var list<EmailValidationResult> */
    public array $results;

    /** @var list<EmailValidationResult> */
    public array $failing_results;

    public readonly int $total;

    public readonly int $failed;

    /** @param list<EmailValidationResult> $results */
    public function __construct(array $results)
    {
        $this->results = array_values($results);
        $this->failing_results = array_values(array_filter(
            $this->results,
            static fn (EmailValidationResult $result): bool => !$result->is_valid,
        ));
        $this->total = count($this->results);
        $this->failed = count($this->failing_results);
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getFailed(): int
    {
        return $this->failed;
    }
}
