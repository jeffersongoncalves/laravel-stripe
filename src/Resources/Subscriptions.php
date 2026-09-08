<?php

namespace JeffersonGoncalves\Stripe\Resources;

use InvalidArgumentException;

class Subscriptions extends Resource
{
    protected string $path = '/v1/subscriptions';

    /**
     * @param  array<int, array<string, mixed>>  $items  e.g. [['price' => 'price_123', 'quantity' => 1]]
     * @param  array<string, mixed>  $attributes
     */
    public function create(string $customerId, array $items, array $attributes = []): array
    {
        if ($items === []) {
            throw new InvalidArgumentException('At least one item is required to create a subscription.');
        }

        return $this->client->post($this->path, array_merge([
            'customer' => $customerId,
            'items' => $items,
        ], $attributes));
    }

    /** @param array<string, mixed> $attributes */
    public function update(string $id, array $attributes): array
    {
        return $this->client->post($this->path.'/'.$id, $attributes);
    }

    /** Cancel immediately. Pass $atPeriodEnd to let the current period run out instead. */
    public function cancel(string $id, bool $atPeriodEnd = false): array
    {
        if ($atPeriodEnd) {
            return $this->update($id, ['cancel_at_period_end' => 'true']);
        }

        return $this->client->delete($this->path.'/'.$id);
    }

    /** Undo a pending "cancel at period end". */
    public function resume(string $id): array
    {
        return $this->update($id, ['cancel_at_period_end' => 'false']);
    }

    /** @param array<string, mixed> $params */
    public function forCustomer(string $customerId, array $params = []): array
    {
        return $this->list(array_merge(['customer' => $customerId], $params));
    }
}
