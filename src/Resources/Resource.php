<?php

namespace JeffersonGoncalves\Stripe\Resources;

use JeffersonGoncalves\Stripe\StripeClient;

/**
 * Shared list/get behaviour for the Stripe resources that expose both a
 * collection endpoint and a GET by id.
 */
abstract class Resource
{
    /** Collection path, e.g. "/v1/customers". */
    protected string $path;

    public function __construct(
        protected StripeClient $client,
        protected int $defaultLimit = 10,
    ) {}

    /**
     * @param  array<string, mixed>  $params  Stripe filters: limit, starting_after, ending_before, created, ...
     */
    public function list(array $params = []): array
    {
        return $this->client->get($this->path, array_merge(['limit' => $this->defaultLimit], $params));
    }

    public function get(string $id): array
    {
        return $this->client->get($this->path.'/'.$id);
    }
}
