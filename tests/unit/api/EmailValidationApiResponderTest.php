<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use andmemasin\emailsvalidator\api\EmailValidationApiResponder;
use andmemasin\emailsvalidator\validation\EmailValidationException;
use andmemasin\emailsvalidator\validation\EmailValidationReport;
use andmemasin\emailsvalidator\validation\EmailValidationResult;
use Codeception\Test\Unit;

final class EmailValidationApiResponderTest extends Unit
{
    public function testSuccessResponseHasStableShapeAndFiltering(): void
    {
        $this->assertResponderClassesAvailable();
        $report = new EmailValidationReport([$this->validResult(), $this->failedResult()]);
        $responder = new EmailValidationApiResponder();

        $all = $responder->success($report, false);
        self::assertSame(200, $all['status']);
        self::assertSame(2, $all['body']['meta']['total']);
        self::assertSame(1, $all['body']['meta']['failed']);
        self::assertFalse($all['body']['meta']['display_only_problems']);
        self::assertCount(2, $all['body']['data']);
        self::assertSame([
            'address',
            'needs_trimming',
            'is_valid',
            'is_valid_rfc',
            'is_no_rfc_warnings',
            'is_valid_dns',
            'is_valid_spoof_check',
        ], array_keys($all['body']['data'][0]));

        $failedOnly = $responder->success($report, true);
        self::assertSame(200, $failedOnly['status']);
        self::assertTrue($failedOnly['body']['meta']['display_only_problems']);
        self::assertCount(1, $failedOnly['body']['data']);
        self::assertSame('not-an-email', $failedOnly['body']['data'][0]['address']);
        self::assertSame(2, $failedOnly['body']['meta']['total']);
        self::assertSame(1, $failedOnly['body']['meta']['failed']);
    }

    public function testValidationErrorsHave422FieldKeyedBodyWithoutExceptionDetails(): void
    {
        $this->assertResponderClassesAvailable();
        $exception = new EmailValidationException([
            'textInput' => ['The e-mail input is required.'],
        ]);

        $response = (new EmailValidationApiResponder())->error($exception);

        self::assertSame(422, $response['status']);
        self::assertSame(['errors' => [
            'textInput' => ['The e-mail input is required.'],
        ]], $response['body']);
        self::assertStringNotContainsString('Exception', json_encode($response));
        self::assertStringNotContainsString('not-an-email', json_encode($response));
    }

    private function validResult(): EmailValidationResult
    {
        return new EmailValidationResult(
            'good@example.com',
            false,
            true,
            true,
            true,
            true,
            true,
        );
    }

    private function failedResult(): EmailValidationResult
    {
        return new EmailValidationResult(
            'not-an-email',
            false,
            false,
            false,
            false,
            true,
            true,
        );
    }

    private function assertResponderClassesAvailable(): void
    {
        self::assertTrue(class_exists(EmailValidationApiResponder::class), 'API responder is not implemented.');
        self::assertTrue(class_exists(EmailValidationReport::class), 'Validation report is not implemented.');
        self::assertTrue(class_exists(EmailValidationResult::class), 'Validation result is not implemented.');
        self::assertTrue(class_exists(EmailValidationException::class), 'Validation exception is not implemented.');
    }
}
