<?php

namespace JeffersonGoncalves\Stripe\Resources;

use JeffersonGoncalves\Stripe\StripeClient;

/**
 * Customer portal sessions — the hosted page where a customer manages their
 * own subscription, payment method and invoices.
 *
 * Sessions are write-only: Stripe exposes no list or GET endpoint for them,
 * so this resource does not extend Resource.
 */
class BillingPortal
{
    public function __construct(
        protected StripeClient $client,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function createSession(string $customerId, string $returnUrl, array $attributes = []): array
    {
        return $this->client->post('/v1/billing_portal/sessions', array_merge([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ], $attributes));
    }
}
