<?php

namespace JeffersonGoncalves\Stripe\Resources;

use InvalidArgumentException;

class Customers extends Resource
{
    protected string $path = '/v1/customers';

    /** @param array<string, mixed> $attributes */
    public function create(string $email, array $attributes = []): array
    {
        if ($email === '') {
            throw new InvalidArgumentException('The "email" attribute is required.');
        }

        return $this->client->post($this->path, array_merge(['email' => $email], $attributes));
    }

    /** @param array<string, mixed> $attributes */
    public function update(string $id, array $attributes): array
    {
        return $this->client->post($this->path.'/'.$id, $attributes);
    }

    public function delete(string $id): array
    {
        return $this->client->delete($this->path.'/'.$id);
    }

    /** The first customer with this email, or null. Stripe does not enforce uniqueness. */
    public function findByEmail(string $email): ?array
    {
        $customers = $this->list(['email' => $email, 'limit' => 1]);

        return $customers['data'][0] ?? null;
    }
}
