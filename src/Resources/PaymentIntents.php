<?php

namespace JeffersonGoncalves\Stripe\Resources;

class PaymentIntents extends Resource
{
    protected string $path = '/v1/payment_intents';

    /**
     * @param  int  $amount  In the currency's smallest unit — 1990 = US$ 19.90.
     * @param  array<string, mixed>  $attributes
     */
    public function create(int $amount, string $currency, array $attributes = []): array
    {
        return $this->client->post($this->path, array_merge([
            'amount' => $amount,
            'currency' => strtolower($currency),
        ], $attributes));
    }

    /** @param array<string, mixed> $attributes */
    public function update(string $id, array $attributes): array
    {
        return $this->client->post($this->path.'/'.$id, $attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function confirm(string $id, array $attributes = []): array
    {
        return $this->client->post($this->path.'/'.$id.'/confirm', $attributes);
    }

    /**
     * Capture an intent authorised with capture_method=manual.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function capture(string $id, array $attributes = []): array
    {
        return $this->client->post($this->path.'/'.$id.'/capture', $attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function cancel(string $id, array $attributes = []): array
    {
        return $this->client->post($this->path.'/'.$id.'/cancel', $attributes);
    }
}
