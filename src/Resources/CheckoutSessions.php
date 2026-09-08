<?php

namespace JeffersonGoncalves\Stripe\Resources;

use InvalidArgumentException;

class CheckoutSessions extends Resource
{
    protected string $path = '/v1/checkout/sessions';

    /**
     * @param  array<int, array<string, mixed>>  $lineItems  e.g. [['price' => 'price_123', 'quantity' => 1]]
     * @param  string  $mode  "payment", "subscription" or "setup".
     * @param  array<string, mixed>  $attributes  e.g. ['customer' => 'cus_123', 'cancel_url' => '...']
     */
    public function create(array $lineItems, string $mode, string $successUrl, array $attributes = []): array
    {
        if ($lineItems === []) {
            throw new InvalidArgumentException('At least one line item is required to create a checkout session.');
        }

        return $this->client->post($this->path, array_merge([
            'line_items' => $lineItems,
            'mode' => $mode,
            'success_url' => $successUrl,
        ], $attributes));
    }

    public function expire(string $id): array
    {
        return $this->client->post($this->path.'/'.$id.'/expire');
    }

    /**
     * The items a completed session was paid for — not included in the session object itself.
     *
     * @param  array<string, mixed>  $params
     */
    public function lineItems(string $id, array $params = []): array
    {
        return $this->client->get($this->path.'/'.$id.'/line_items', $params);
    }
}
