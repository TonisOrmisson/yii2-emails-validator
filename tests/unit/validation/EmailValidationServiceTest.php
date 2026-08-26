<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use andmemasin\emailsvalidator\validation\EmailValidationException;
use andmemasin\emailsvalidator\validation\EmailValidationRequest;
use andmemasin\emailsvalidator\validation\EmailValidationService;
use Codeception\Test\Unit;

final class EmailValidationServiceTest extends Unit
{
    public function testSingleAddressPreservesValidationFlagsAndOptions(): void
    {
        $service = $this->service();
        $report = $service->validate($this->request('good@example.com', false, false, false));

        self::assertCount(1, $report->results);
        $result = $report->results[0];
        self::assertSame('good@example.com', $result->address);
        self::assertFalse($result->needs_trimming);
        self::assertTrue($result->is_valid);
        self::assertTrue($result->is_valid_rfc);
        self::assertTrue($result->is_no_rfc_warnings);
        self::assertTrue($result->is_valid_dns);
        self::assertTrue($result->is_valid_spoof_check);
    }

    public function testLineParsingSkipsEmptyLinesAndReportsFailuresInOrder(): void
    {
        $report = $this->service()->validate($this->request(
            "good@example.com\r\n\r\n good@example.com\nnot-an-email",
            false,
            false,
            false,
        ));

        self::assertSame(3, $report->total);
        self::assertSame(2, $report->failed);
        self::assertSame(
            ['good@example.com', ' good@example.com', 'not-an-email'],
            array_map(static fn ($result): string => $result->address, $report->results),
        );
        self::assertSame([' good@example.com', 'not-an-email'], array_map(
            static fn ($result): string => $result->address,
            $report->failing_results,
        ));
        self::assertTrue($report->results[1]->needs_trimming);
        self::assertFalse($report->results[1]->is_valid);
    }

    public function testInputBoundaryRejectsInvalidFieldsAndEnforcesMaximum(): void
    {
        $this->assertValidationClassesAvailable();

        foreach ([
            [[], ['textInput', 'checkDNS', 'checkSpoof', 'displayOnlyProblems']],
            [['textInput' => ''], ['textInput', 'checkDNS', 'checkSpoof', 'displayOnlyProblems']],
            [['textInput' => null], ['textInput', 'checkDNS', 'checkSpoof', 'displayOnlyProblems']],
            [['textInput' => 123], ['textInput', 'checkDNS', 'checkSpoof', 'displayOnlyProblems']],
            [['textInput' => 'good@example.com', 'checkDNS' => 'yes'], ['checkDNS', 'checkSpoof', 'displayOnlyProblems']],
            [['textInput' => 'good@example.com', 'checkSpoof' => 1], ['checkDNS', 'checkSpoof', 'displayOnlyProblems']],
            [['textInput' => 'good@example.com', 'displayOnlyProblems' => 0], ['checkDNS', 'checkSpoof', 'displayOnlyProblems']],
        ] as [$payload, $fields]) {
            try {
                EmailValidationRequest::fromArray($payload);
                self::fail('Invalid input was accepted.');
            } catch (EmailValidationException $exception) {
                self::assertSame($fields, array_keys($exception->errors()));
            }
        }

        $service = new EmailValidationService(8);
        $service->validate($this->request(str_repeat('x', 8), false, false, false));

        try {
            $service->validate($this->request(str_repeat('x', 9), false, false, false));
            self::fail('Over-limit input was accepted.');
        } catch (EmailValidationException $exception) {
            self::assertSame(['textInput'], array_keys($exception->errors()));
        }
    }

    public function testInputBoundaryRejectsUnknownPropertiesAsFieldErrors(): void
    {
        $payload = [
            'textInput' => 'good@example.com',
            'checkDNS' => false,
            'checkSpoof' => false,
            'displayOnlyProblems' => false,
            'unknown' => 'not-allowed',
        ];

        try {
            EmailValidationRequest::fromArray($payload);
            self::fail('Unknown input properties must be rejected.');
        } catch (EmailValidationException $exception) {
            self::assertSame(['unknown'], array_keys($exception->errors()));
        }
    }

    public function testServiceHasNoFrameworkDependency(): void
    {
        $this->assertValidationClassesAvailable();
        $source = file_get_contents(dirname(__DIR__, 3) . '/src/validation/EmailValidationService.php');

        self::assertIsString($source);
        self::assertStringNotContainsString('Yii', $source);
        self::assertStringNotContainsString('Illuminate', $source);
        self::assertStringNotContainsString('ActiveRecord', $source);
        self::assertStringNotContainsString('Controller', $source);
    }

    private function service(): EmailValidationService
    {
        $this->assertValidationClassesAvailable();

        return new EmailValidationService(128 * 1024);
    }

    private function request(
        string $textInput,
        bool $checkDNS,
        bool $checkSpoof,
        bool $displayOnlyProblems,
    ): EmailValidationRequest {
        $this->assertValidationClassesAvailable();

        return new EmailValidationRequest($textInput, $checkDNS, $checkSpoof, $displayOnlyProblems);
    }

    private function assertValidationClassesAvailable(): void
    {
        self::assertTrue(class_exists(EmailValidationRequest::class), 'Neutral request is not implemented.');
        self::assertTrue(class_exists(EmailValidationService::class), 'Neutral service is not implemented.');
        self::assertTrue(class_exists(EmailValidationException::class), 'Neutral validation exception is not implemented.');
    }
}
