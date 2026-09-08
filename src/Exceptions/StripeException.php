<?php

namespace JeffersonGoncalves\Stripe\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

class StripeException extends RuntimeException
{
    /** @var array<string, mixed> */
    protected array $errorBody = [];

    public static function fromResponse(Response $response): self
    {
        $body = (array) ($response->json() ?? []);

        $message = $body['error']['message']
            ?? $body['error']['code']
            ?? "Stripe API error (HTTP {$response->status()}).";

        $exception = new self((string) $message, $response->status());
        $exception->errorBody = $body;

        return $exception;
    }

    /** @return array<string, mixed> */
    public function errorBody(): array
    {
        return $this->errorBody;
    }

    /** Stripe's machine-readable error code, e.g. "resource_missing". */
    public function errorCode(): ?string
    {
        $code = $this->errorBody['error']['code'] ?? null;

        return is_string($code) ? $code : null;
    }

    /** Stripe's error type, e.g. "card_error", "invalid_request_error". */
    public function errorType(): ?string
    {
        $type = $this->errorBody['error']['type'] ?? null;

        return is_string($type) ? $type : null;
    }

    /** The request parameter Stripe complained about, when it named one. */
    public function param(): ?string
    {
        $param = $this->errorBody['error']['param'] ?? null;

        return is_string($param) ? $param : null;
    }
}
