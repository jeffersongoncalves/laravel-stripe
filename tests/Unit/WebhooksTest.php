<?php

use Illuminate\Http\Request;
use JeffersonGoncalves\Stripe\Facades\Stripe;

function stripeSignature(string $payload, string $secret = 'whsec_test', ?int $timestamp = null): string
{
    $timestamp ??= time();

    return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
}

it('accepts a signature built with the configured secret', function () {
    $payload = '{"type":"checkout.session.completed"}';

    expect(Stripe::webhooks()->verify($payload, stripeSignature($payload)))->toBeTrue();
});

it('rejects a signature made with the wrong secret', function () {
    $payload = '{"type":"checkout.session.completed"}';

    expect(Stripe::webhooks()->verify($payload, stripeSignature($payload, 'whsec_other')))->toBeFalse();
});

it('rejects a tampered payload', function () {
    $signature = stripeSignature('{"amount":100}');

    expect(Stripe::webhooks()->verify('{"amount":999}', $signature))->toBeFalse();
});

it('rejects a malformed signature header', function () {
    expect(Stripe::webhooks()->verify('{}', 'nonsense'))->toBeFalse();
});

it('rejects a stale timestamp but accepts it when the tolerance check is disabled', function () {
    $payload = '{}';
    $signature = stripeSignature($payload, 'whsec_test', time() - 3600);

    expect(Stripe::webhooks()->verify($payload, $signature))->toBeFalse()
        ->and(Stripe::webhooks()->verify($payload, $signature, tolerance: 0))->toBeTrue();
});

it('accepts any of several v1 signatures during a secret rotation', function () {
    $payload = '{"type":"invoice.paid"}';
    $timestamp = time();

    $header = 't='.$timestamp
        .',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_old')
        .',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test');

    expect(Stripe::webhooks()->verify($payload, $header))->toBeTrue();
});

it('verifies straight off a request', function () {
    $payload = '{"type":"customer.subscription.deleted"}';

    $request = Request::create('/stripe/webhook', 'POST', [], [], [], [], $payload);
    $request->headers->set('Stripe-Signature', stripeSignature($payload));

    expect(Stripe::webhooks()->verifyRequest($request))->toBeTrue();
});

it('refuses to verify when no secret is configured', function () {
    config(['stripe.webhook_secret' => null]);
    app()->forgetInstance(JeffersonGoncalves\Stripe\Stripe::class);
    Stripe::clearResolvedInstances();

    Stripe::webhooks()->verify('{}', stripeSignature('{}'));
})->throws(InvalidArgumentException::class, 'No Stripe webhook secret configured. Set STRIPE_WEBHOOK_SECRET.');
