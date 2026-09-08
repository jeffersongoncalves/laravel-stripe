<?php

namespace JeffersonGoncalves\Stripe\Resources;

class Invoices extends Resource
{
    protected string $path = '/v1/invoices';

    /** @param array<string, mixed> $params */
    public function forCustomer(string $customerId, array $params = []): array
    {
        return $this->list(array_merge(['customer' => $customerId], $params));
    }

    /** @param array<string, mixed> $attributes */
    public function pay(string $id, array $attributes = []): array
    {
        return $this->client->post($this->path.'/'.$id.'/pay', $attributes);
    }

    /** Send the invoice email to the customer (manual collection only). */
    public function send(string $id): array
    {
        return $this->client->post($this->path.'/'.$id.'/send');
    }

    /** Void an open invoice — a finalized invoice cannot be deleted. */
    public function void(string $id): array
    {
        return $this->client->post($this->path.'/'.$id.'/void');
    }
}
