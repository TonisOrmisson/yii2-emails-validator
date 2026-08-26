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
        $servers = array_column($document['servers'] ?? [], 'url');
        self::assertContains('/', $servers);
        self::assertContains('/emailsvalidator', $servers);
        $serverRoutes = array_map(
            static fn (string $server): string => rtrim($server, '/') . '/api/v1/email-validations',
            $servers,
        );
        self::assertContains('/api/v1/email-validations', $serverRoutes);
        self::assertContains('/emailsvalidator/api/v1/email-validations', $serverRoutes);
        self::assertArrayHasKey('/api/v1/email-validations', $document['paths']);
        self::assertCount(1, $document['paths']);

        $operation = $document['paths']['/api/v1/email-validations'];
        self::assertSame(['post'], array_keys($operation));
        self::assertArrayHasKey('security', $operation['post']);
        self::assertSame([['cookieAuth' => []]], $operation['post']['security']);
        self::assertArrayHasKey('requestBody', $operation['post']);
        $csrfParameters = array_values(array_filter(
            $operation['post']['parameters'] ?? [],
            static fn (array $parameter): bool => ($parameter['name'] ?? null) === 'X-CSRF-Token'
                && ($parameter['in'] ?? null) === 'header',
        ));
        self::assertCount(1, $csrfParameters);
        self::assertTrue($csrfParameters[0]['required'] ?? false);
        self::assertSame(['type' => 'string'], $csrfParameters[0]['schema']);
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
        self::assertSame(
            ['address', 'needs_trimming', 'is_valid', 'is_valid_rfc', 'is_no_rfc_warnings', 'is_valid_dns', 'is_valid_spoof_check'],
            $schemas['EmailValidationResult']['required'],
        );

        $securitySchemes = $document['components']['securitySchemes'];
        self::assertSame('apiKey', $securitySchemes['cookieAuth']['type']);
        self::assertSame('header', $securitySchemes['cookieAuth']['in']);
        self::assertSame('Cookie', $securitySchemes['cookieAuth']['name']);
    }

    public function testOpenApiDocumentsConfigurableInputLimitInsteadOfUniversalByteLimit(): void
    {
        $path = dirname(__DIR__, 3) . '/resources/openapi/email-validation-v1.json';
        $document = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $schema = $document['components']['schemas']['EmailValidationRequest'];

        self::assertArrayNotHasKey('maxLength', $schema['properties']['textInput']);
        $description = $document['info']['description'] ?? '';
        self::assertStringContainsString('128 KB', $description);
        self::assertStringContainsString('configur', strtolower($description));
    }
}
