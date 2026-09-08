<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Stripe\Facades\Stripe;

it('lists the invoices of one customer', function () {
    Http::fake(['*/v1/invoices*' => Http::response(['data' => []])]);

    Stripe::invoices()->forCustomer('cus_123', ['status' => 'paid']);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'customer=cus_123')
        && str_contains($request->url(), 'status=paid'));
});

it('pays, sends and voids an invoice', function () {
    Http::fake(['*/v1/invoices/in_123/*' => Http::response(['id' => 'in_123'])]);

    Stripe::invoices()->pay('in_123');
    Stripe::invoices()->send('in_123');
    Stripe::invoices()->void('in_123');

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/pay'));
    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/send'));
    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/void'));
});

it('creates and captures a payment intent', function () {
    Http::fake([
        '*/v1/payment_intents/pi_123/*' => Http::response(['id' => 'pi_123', 'status' => 'succeeded']),
        '*/v1/payment_intents*' => Http::response(['id' => 'pi_123']),
    ]);

    Stripe::paymentIntents()->create(2500, 'BRL', ['capture_method' => 'manual']);
    Http::assertSent(fn ($request) => $request['amount'] === 2500 && $request['currency'] === 'brl');

    expect(Stripe::paymentIntents()->capture('pi_123')['status'])->toBe('succeeded');

    Stripe::paymentIntents()->cancel('pi_123');
    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/cancel'));
});

it('filters events by type', function () {
    Http::fake(['*/v1/events*' => Http::response(['data' => []])]);

    Stripe::events()->ofType('checkout.session.completed');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'type=checkout.session.completed'));
});

it('pins the API version when one is configured', function () {
    Http::fake(['*' => Http::response(['data' => []])]);

    config(['stripe.api_version' => '2025-03-31.basil']);
    app()->forgetInstance(JeffersonGoncalves\Stripe\Stripe::class);
    Stripe::clearResolvedInstances();

    Stripe::events()->list();

    Http::assertSent(fn ($request) => $request->hasHeader('Stripe-Version', '2025-03-31.basil'));
});
