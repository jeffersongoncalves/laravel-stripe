<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Stripe\Facades\Stripe;

it('creates a subscription', function () {
    Http::fake(['*/v1/subscriptions' => Http::response(['id' => 'sub_123'])]);

    Stripe::subscriptions()->create('cus_123', [['price' => 'price_123', 'quantity' => 2]]);

    Http::assertSent(fn ($request) => $request['customer'] === 'cus_123'
        && $request['items'][0]['price'] === 'price_123'
        && $request['items'][0]['quantity'] === 2);
});

it('refuses to create a subscription with no items', function () {
    Stripe::subscriptions()->create('cus_123', []);
})->throws(InvalidArgumentException::class, 'At least one item is required to create a subscription.');

it('cancels immediately by default and at period end on request', function () {
    Http::fake(['*/v1/subscriptions/sub_123' => Http::response(['id' => 'sub_123'])]);

    Stripe::subscriptions()->cancel('sub_123');
    Http::assertSent(fn ($request) => $request->method() === 'DELETE');

    Stripe::subscriptions()->cancel('sub_123', atPeriodEnd: true);
    Http::assertSent(fn ($request) => $request->method() === 'POST' && $request['cancel_at_period_end'] === 'true');
});

it('resumes a subscription pending cancellation', function () {
    Http::fake(['*/v1/subscriptions/sub_123' => Http::response(['id' => 'sub_123'])]);

    Stripe::subscriptions()->resume('sub_123');

    Http::assertSent(fn ($request) => $request['cancel_at_period_end'] === 'false');
});

it('lists the subscriptions of one customer', function () {
    Http::fake(['*/v1/subscriptions*' => Http::response(['data' => []])]);

    Stripe::subscriptions()->forCustomer('cus_123', ['status' => 'active']);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'customer=cus_123')
        && str_contains($request->url(), 'status=active'));
});
