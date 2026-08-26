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
            $payload = $request->all();
            $validationRequest = EmailValidationRequest::fromArray(is_array($payload) ? $payload : []);
            $response = $this->responder->success(
                $this->service->validate($validationRequest),
                $validationRequest->displayOnlyProblems,
            );
        } catch (EmailValidationException $exception) {
            $response = $this->responder->error($exception);
        }

        return new JsonResponse($response['body'], $response['status']);
    }
}
