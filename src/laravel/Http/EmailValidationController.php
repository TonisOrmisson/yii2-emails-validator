<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator\laravel\Http;

use andmemasin\emailsvalidator\api\EmailValidationApiResponder;
use andmemasin\emailsvalidator\validation\EmailValidationException;
use andmemasin\emailsvalidator\validation\EmailValidationRequest;
use andmemasin\emailsvalidator\validation\EmailValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmailValidationController
{
    public function __construct(
        private readonly EmailValidationService $service,
        private readonly EmailValidationApiResponder $responder,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payload = $this->decodeJsonObject($request->getContent());
            $validationRequest = EmailValidationRequest::fromArray($payload);
            $response = $this->responder->success(
                $this->service->validate($validationRequest),
                $validationRequest->displayOnlyProblems,
            );
        } catch (EmailValidationException $exception) {
            $response = $this->responder->error($exception);
        }

        return new JsonResponse($response['body'], $response['status']);
    }

    /** @return array<string, mixed> */
    private function decodeJsonObject(string $body): array
    {
        try {
            $decoded = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $decoded = null;
        }

        if (!$decoded instanceof \stdClass) {
            throw new EmailValidationException([
                'request' => ['The request body must be a valid JSON object.'],
            ]);
        }

        return (array) $decoded;
    }
}
