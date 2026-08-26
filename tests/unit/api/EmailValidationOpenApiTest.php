<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use Codeception\Test\Unit;

final class EmailValidationOpenApiTest extends Unit
{
    public function testOpenApiDescribesOnlyTheProtectedPostValidationContract(): void
    {
        $path = dirname(__DIR__, 3) . '/resources/openapi/email-validation-v1.json';
        self::assertFileExists($path, 'The EmailsValidator OpenAPI contract is missing.');

        $document = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('3.1.0', $document['openapi']);
        self::assertArrayHasKey('/api/v1/email-validations', $document['paths']);
        self::assertCount(1, $document['paths']);

        $operation = $document['paths']['/api/v1/email-validations'];
        self::assertSame(['post'], array_keys($operation));
        self::assertArrayHasKey('security', $operation['post']);
        self::assertSame([['cookieAuth' => []]], $operation['post']['security']);
        self::assertArrayHasKey('requestBody', $operation['post']);
        self::assertArrayHasKey('responses', $operation['post']);
        self::assertArrayHasKey('200', $operation['post']['responses']);
        self::assertArrayHasKey('422', $operation['post']['responses']);

        $schemas = $document['components']['schemas'];
        self::assertArrayHasKey('EmailValidationRequest', $schemas);
        self::assertArrayHasKey('EmailValidationResult', $schemas);
        self::assertArrayHasKey('EmailValidationResponse', $schemas);
        self::assertArrayHasKey('EmailValidationErrorResponse', $schemas);
        self::assertSame(
            ['textInput', 'checkDNS', 'checkSpoof', 'displayOnlyProblems'],
            $schemas['EmailValidationRequest']['required'],
        );
        self::assertSame(131072, $schemas['EmailValidationRequest']['properties']['textInput']['maxLength']);
        self::assertSame(
            ['address', 'needs_trimming', 'is_valid', 'is_valid_rfc', 'is_no_rfc_warnings', 'is_valid_dns', 'is_valid_spoof_check'],
            $schemas['EmailValidationResult']['required'],
        );

        $securitySchemes = $document['components']['securitySchemes'];
        self::assertSame('apiKey', $securitySchemes['cookieAuth']['type']);
        self::assertSame('header', $securitySchemes['cookieAuth']['in']);
        self::assertSame('Cookie', $securitySchemes['cookieAuth']['name']);
    }
}
