<?php

namespace JeffersonGoncalves\Stripe\Resources;

use InvalidArgumentException;

class Prices extends Resource
{
    protected string $path = '/v1/prices';

    /**
     * @param  int  $unitAmount  In the currency's smallest unit — 1990 = US$ 19.90.
     * @param  array<string, mixed>  $attributes  e.g. ['recurring' => ['interval' => 'month']]
     */
    public function create(string $productId, int $unitAmount, string $currency, array $attributes = []): array
    {
        if ($productId === '') {
            throw new InvalidArgumentException('The "product" attribute is required.');
        }

        return $this->client->post($this->path, array_merge([
            'product' => $productId,
            'unit_amount' => $unitAmount,
            'currency' => strtolower($currency),
        ], $attributes));
    }

    /**
     * Prices are immutable apart from metadata, nickname and active.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(string $id, array $attributes): array
    {
        return $this->client->post($this->path.'/'.$id, $attributes);
    }

    public function archive(string $id): array
    {
        return $this->update($id, ['active' => 'false']);
    }
}
