<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Stripe\Exceptions\StripeException;
use JeffersonGoncalves\Stripe\Facades\Stripe;

it('creates a customer', function () {
    Http::fake(['*/v1/customers' => Http::response(['id' => 'cus_123'], 200)]);

    Stripe::customers()->create('jane@example.com', ['name' => 'Jane']);

    Http::assertSent(fn ($request) => $request['email'] === 'jane@example.com' && $request['name'] === 'Jane');
});

it('requires an email to create a customer', function () {
    Stripe::customers()->create('');
})->throws(InvalidArgumentException::class, 'The "email" attribute is required.');

it('lists, gets, updates and deletes a customer', function () {
    Http::fake([
        '*/v1/customers/cus_123' => Http::response(['id' => 'cus_123']),
        '*/v1/customers*' => Http::response(['data' => []]),
    ]);

    Stripe::customers()->list();
    expect(Stripe::customers()->get('cus_123')['id'])->toBe('cus_123');
    Stripe::customers()->update('cus_123', ['name' => 'Janet']);
    Stripe::customers()->delete('cus_123');

    Http::assertSent(fn ($request) => $request->method() === 'POST' && ($request['name'] ?? null) === 'Janet');
    Http::assertSent(fn ($request) => $request->method() === 'DELETE');
});

it('finds a customer by email', function () {
    Http::fake(['*/v1/customers*' => Http::response(['data' => [['id' => 'cus_123']]])]);

    expect(Stripe::customers()->findByEmail('jane@example.com')['id'])->toBe('cus_123');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'email=jane%40example.com'));
});

it('returns null when no customer has that email', function () {
    Http::fake(['*/v1/customers*' => Http::response(['data' => []])]);

    expect(Stripe::customers()->findByEmail('nobody@example.com'))->toBeNull();
});

it('sends the secret key as a bearer token', function () {
    Http::fake(['*' => Http::response(['data' => []])]);

    Stripe::customers()->list();

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer sk_test_key'));
});

it('throws a StripeException on a failed response', function () {
    Http::fake(['*' => Http::response([
        'error' => ['type' => 'invalid_request_error', 'code' => 'resource_missing', 'message' => 'No such customer: cus_x'],
    ], 404)]);

    Stripe::customers()->get('cus_x');
})->throws(StripeException::class, 'No such customer: cus_x');

it('defaults the list limit from config', function () {
    Http::fake(['*' => Http::response(['data' => []])]);

    Stripe::customers()->list();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'limit=10'));
});
