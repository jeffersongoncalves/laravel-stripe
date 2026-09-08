<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Stripe\Facades\Stripe;

it('creates, updates and archives a product', function () {
    Http::fake([
        '*/v1/products/prod_123' => Http::response(['id' => 'prod_123']),
        '*/v1/products*' => Http::response(['id' => 'prod_123']),
    ]);

    Stripe::products()->create('Pro Plan', ['description' => 'Everything in one plan']);
    Http::assertSent(fn ($request) => $request['name'] === 'Pro Plan');

    Stripe::products()->update('prod_123', ['name' => 'Pro Plan (annual)']);
    Stripe::products()->archive('prod_123');
    Http::assertSent(fn ($request) => ($request['active'] ?? null) === 'false');

    Stripe::products()->delete('prod_123');
    Http::assertSent(fn ($request) => $request->method() === 'DELETE');
});

it('requires a name to create a product', function () {
    Stripe::products()->create('');
})->throws(InvalidArgumentException::class, 'The "name" attribute is required.');

it('creates a recurring price and lowercases the currency', function () {
    Http::fake(['*/v1/prices' => Http::response(['id' => 'price_123'])]);

    Stripe::prices()->create('prod_123', 1990, 'USD', [
        'recurring' => ['interval' => 'month'],
    ]);

    Http::assertSent(fn ($request) => $request['product'] === 'prod_123'
        && $request['unit_amount'] === 1990
        && $request['currency'] === 'usd'
        && $request['recurring']['interval'] === 'month');
});

it('requires a product to create a price', function () {
    Stripe::prices()->create('', 100, 'usd');
})->throws(InvalidArgumentException::class, 'The "product" attribute is required.');

it('archives a price', function () {
    Http::fake(['*/v1/prices/price_123' => Http::response(['id' => 'price_123'])]);

    Stripe::prices()->archive('price_123');

    Http::assertSent(fn ($request) => $request['active'] === 'false');
});
