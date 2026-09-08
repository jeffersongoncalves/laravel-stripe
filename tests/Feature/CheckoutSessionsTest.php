<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Stripe\Facades\Stripe;

it('creates a checkout session', function () {
    Http::fake(['*/v1/checkout/sessions' => Http::response(['id' => 'cs_123', 'url' => 'https://checkout.stripe.com/c/pay/cs_123'])]);

    $session = Stripe::checkout()->create(
        lineItems: [['price' => 'price_123', 'quantity' => 1]],
        mode: 'subscription',
        successUrl: 'https://example.com/success',
        attributes: ['customer' => 'cus_123', 'cancel_url' => 'https://example.com/cancel'],
    );

    expect($session['url'])->toBe('https://checkout.stripe.com/c/pay/cs_123');

    Http::assertSent(fn ($request) => $request['mode'] === 'subscription'
        && $request['line_items'][0]['price'] === 'price_123'
        && $request['success_url'] === 'https://example.com/success'
        && $request['customer'] === 'cus_123');
});

it('refuses to create a checkout session with no line items', function () {
    Stripe::checkout()->create([], 'payment', 'https://example.com/success');
})->throws(InvalidArgumentException::class, 'At least one line item is required to create a checkout session.');

it('expires a session and reads its line items', function () {
    Http::fake([
        '*/v1/checkout/sessions/cs_123/expire' => Http::response(['status' => 'expired']),
        '*/v1/checkout/sessions/cs_123/line_items*' => Http::response(['data' => [['id' => 'li_123']]]),
    ]);

    expect(Stripe::checkout()->expire('cs_123')['status'])->toBe('expired')
        ->and(Stripe::checkout()->lineItems('cs_123')['data'][0]['id'])->toBe('li_123');
});

it('creates a billing portal session', function () {
    Http::fake(['*/v1/billing_portal/sessions' => Http::response(['url' => 'https://billing.stripe.com/p/session/x'])]);

    $session = Stripe::billingPortal()->createSession('cus_123', 'https://example.com/account');

    expect($session['url'])->toBe('https://billing.stripe.com/p/session/x');

    Http::assertSent(fn ($request) => $request['customer'] === 'cus_123'
        && $request['return_url'] === 'https://example.com/account');
});
