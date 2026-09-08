<?php

namespace JeffersonGoncalves\Stripe;

use JeffersonGoncalves\Stripe\Resources\BillingPortal;
use JeffersonGoncalves\Stripe\Resources\CheckoutSessions;
use JeffersonGoncalves\Stripe\Resources\Customers;
use JeffersonGoncalves\Stripe\Resources\Events;
use JeffersonGoncalves\Stripe\Resources\Invoices;
use JeffersonGoncalves\Stripe\Resources\PaymentIntents;
use JeffersonGoncalves\Stripe\Resources\Prices;
use JeffersonGoncalves\Stripe\Resources\Products;
use JeffersonGoncalves\Stripe\Resources\Subscriptions;

/**
 * Entry point exposing one resource per Stripe API group, plus webhook
 * signature verification.
 */
class Stripe
{
    protected StripeClient $client;

    public function __construct(
        string $secret,
        string $baseUrl = 'https://api.stripe.com',
        protected int $defaultLimit = 10,
        protected ?string $webhookSecret = null,
        ?string $apiVersion = null,
    ) {
        $this->client = new StripeClient($secret, $baseUrl, $apiVersion);
    }

    public function customers(): Customers
    {
        return new Customers($this->client, $this->defaultLimit);
    }

    public function subscriptions(): Subscriptions
    {
        return new Subscriptions($this->client, $this->defaultLimit);
    }

    public function products(): Products
    {
        return new Products($this->client, $this->defaultLimit);
    }

    public function prices(): Prices
    {
        return new Prices($this->client, $this->defaultLimit);
    }

    public function checkout(): CheckoutSessions
    {
        return new CheckoutSessions($this->client, $this->defaultLimit);
    }

    public function billingPortal(): BillingPortal
    {
        return new BillingPortal($this->client);
    }

    public function invoices(): Invoices
    {
        return new Invoices($this->client, $this->defaultLimit);
    }

    public function paymentIntents(): PaymentIntents
    {
        return new PaymentIntents($this->client, $this->defaultLimit);
    }

    public function events(): Events
    {
        return new Events($this->client, $this->defaultLimit);
    }

    public function webhooks(): Webhooks
    {
        return new Webhooks($this->webhookSecret);
    }
}
