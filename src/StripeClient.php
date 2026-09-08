<?php

namespace JeffersonGoncalves\Stripe;

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Stripe\Exceptions\StripeException;

/**
 * Thin wrapper around Laravel's Http client for the Stripe REST API.
 *
 * Stripe authenticates with a Bearer secret key, takes form-encoded bodies
 * (deep bracket notation, which http_build_query already produces) and uses
 * POST — never PUT/PATCH — to update a resource.
 *
 * ponytail: Http::retry() covers transient-failure retries if ever needed —
 * no custom retry/backoff layer built here speculatively.
 */
class StripeClient
{
    public function __construct(
        protected string $secret,
        protected string $baseUrl = 'https://api.stripe.com',
        protected ?string $apiVersion = null,
    ) {}

    /** @param array<string, mixed> $query */
    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, $query);
    }

    /** @param array<string, mixed> $body */
    public function post(string $path, array $body = []): array
    {
        return $this->request('post', $path, $body);
    }

    /** @param array<string, mixed> $body */
    public function delete(string $path, array $body = []): array
    {
        return $this->request('delete', $path, $body);
    }

    /** @param array<string, mixed> $data */
    protected function request(string $method, string $path, array $data = []): array
    {
        $request = Http::withToken($this->secret)
            ->acceptJson()
            ->baseUrl(rtrim($this->baseUrl, '/'));

        if ($this->apiVersion !== null && $this->apiVersion !== '') {
            $request = $request->withHeaders(['Stripe-Version' => $this->apiVersion]);
        }

        // Stripe rejects JSON bodies; everything but GET goes out form-encoded.
        if ($method !== 'get') {
            $request = $request->asForm();
        }

        $response = $request->{$method}($path, $data);

        if ($response->failed()) {
            throw StripeException::fromResponse($response);
        }

        return (array) ($response->json() ?? []);
    }
}
