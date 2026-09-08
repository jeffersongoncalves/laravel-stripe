<div class="filament-hidden">

![Laravel Stripe](https://raw.githubusercontent.com/jeffersongoncalves/laravel-stripe/main/art/jeffersongoncalves-laravel-stripe.png)

</div>

# Laravel Stripe

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-stripe.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-stripe)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-stripe/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-stripe/actions?query=workflow%3ATests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-stripe/pint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-stripe/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-stripe.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-stripe)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-stripe.svg?style=flat-square)](LICENSE.md)

A PHP/Laravel client for the [Stripe](https://stripe.com/) API. Covers customers, subscriptions, products, prices, checkout sessions, the billing portal, invoices, payment intents and events through a simple API built on Laravel's `Http` client — plus verification of Stripe's webhook signatures.

No `stripe/stripe-php` dependency: just `illuminate/http`, so responses come back as plain arrays and are trivial to fake in tests.

## Features

- Customers: list, get, create, update, delete, find by email
- Subscriptions: list, get, create, update, cancel (now or at period end), resume, list for a customer
- Products: list, get, create, update, archive, delete
- Prices: list, get, create, update, archive
- Checkout: create a session, get, list, expire, line items
- Billing portal: create a customer portal session
- Invoices: list, get, list for a customer, pay, send, void
- Payment intents: list, get, create, update, confirm, capture, cancel
- Events: list, get, filter by type (the 30-day log behind webhooks)
- Webhooks: `Stripe-Signature` verification (HMAC-SHA256, replay-window check, rotation-safe)
- Pin an API version with a single env var
- Throws `StripeException` (with the original error body, code, type and param) on any non-2xx response
- Throws `InvalidArgumentException` before hitting the API when a required field is missing

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/laravel-stripe
```

Publish the config file:

```bash
php artisan vendor:publish --tag=stripe-config
```

Set your credentials in `.env`:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

Find the keys under **Developers > API keys** and the webhook signing secret under **Developers > Webhooks** in the Stripe dashboard. Test and live keys are separate — only `STRIPE_SECRET` is used by this package, `STRIPE_KEY` is kept in the config for your client-side code.

## Configuration

```php
// config/stripe.php
return [
    'secret' => env('STRIPE_SECRET'),
    'key' => env('STRIPE_KEY'),
    'base_url' => env('STRIPE_BASE_URL', 'https://api.stripe.com'),
    'api_version' => env('STRIPE_API_VERSION'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'default_limit' => env('STRIPE_DEFAULT_LIMIT', 10),
];
```

## Usage

Use the `Stripe` facade or inject `JeffersonGoncalves\Stripe\Stripe`. Each API group is exposed as a method returning a dedicated resource class. Every `list()` accepts Stripe's own filters (`limit`, `starting_after`, `ending_before`, `created`, ...) and defaults `limit` to `default_limit`.

Amounts are always in the currency's smallest unit — `1990` is US$ 19.90.

### Customers

```php
use JeffersonGoncalves\Stripe\Facades\Stripe;

$customer = Stripe::customers()->create('jane@example.com', ['name' => 'Jane Doe']);

Stripe::customers()->list(['limit' => 10]);
Stripe::customers()->get($customer['id']);
Stripe::customers()->update($customer['id'], ['name' => 'Janet Doe']);
Stripe::customers()->delete($customer['id']);

// First customer with that email, or null — Stripe does not enforce uniqueness
$existing = Stripe::customers()->findByEmail('jane@example.com');
```

### Products and prices

```php
$product = Stripe::products()->create('Pro Plan', ['description' => 'Everything in one plan']);

$price = Stripe::prices()->create(
    productId: $product['id'],
    unitAmount: 1990,        // US$ 19.90
    currency: 'usd',
    attributes: ['recurring' => ['interval' => 'month']],
);

Stripe::products()->archive($product['id']);   // active = false
Stripe::prices()->archive($price['id']);
```

### Checkout and the billing portal

```php
$session = Stripe::checkout()->create(
    lineItems: [['price' => $price['id'], 'quantity' => 1]],
    mode: 'subscription',
    successUrl: 'https://example.com/success?session_id={CHECKOUT_SESSION_ID}',
    attributes: [
        'customer' => $customer['id'],
        'cancel_url' => 'https://example.com/cancel',
    ],
);

return redirect()->away($session['url']);
```

```php
$portal = Stripe::billingPortal()->createSession($customer['id'], 'https://example.com/account');

return redirect()->away($portal['url']);
```

### Subscriptions

```php
$subscription = Stripe::subscriptions()->create($customer['id'], [
    ['price' => $price['id'], 'quantity' => 1],
]);

Stripe::subscriptions()->forCustomer($customer['id'], ['status' => 'active']);

Stripe::subscriptions()->update($subscription['id'], [
    'items' => [['id' => $subscription['items']['data'][0]['id'], 'quantity' => 3]],
    'proration_behavior' => 'always_invoice',
]);

Stripe::subscriptions()->cancel($subscription['id']);                      // immediately
Stripe::subscriptions()->cancel($subscription['id'], atPeriodEnd: true);   // at period end
Stripe::subscriptions()->resume($subscription['id']);                      // undo the above
```

### Invoices, payment intents and events

```php
Stripe::invoices()->forCustomer($customer['id'], ['status' => 'paid']);
Stripe::invoices()->pay('in_123');
Stripe::invoices()->void('in_123');

$intent = Stripe::paymentIntents()->create(2500, 'brl', [
    'customer' => $customer['id'],
    'automatic_payment_methods' => ['enabled' => 'true'],
]);

Stripe::paymentIntents()->capture($intent['id']);   // capture_method = manual
Stripe::paymentIntents()->cancel($intent['id']);

Stripe::events()->ofType('checkout.session.completed');
```

### Webhooks

Verify the `Stripe-Signature` header against the raw request body before trusting a webhook:

```php
use Illuminate\Http\Request;
use JeffersonGoncalves\Stripe\Facades\Stripe;

Route::post('/stripe/webhook', function (Request $request) {
    abort_unless(Stripe::webhooks()->verifyRequest($request), 403);

    $event = $request->json()->all();

    match ($event['type']) {
        'checkout.session.completed' => /* provision access */ null,
        'customer.subscription.deleted' => /* revoke access */ null,
        'invoice.payment_failed' => /* notify the customer */ null,
        default => null,
    };

    return response()->noContent();
})->withoutMiddleware([VerifyCsrfToken::class]);
```

`verify(string $payload, string $signature, int $tolerance = 300)` is available when you already hold the raw body. Signatures older than `$tolerance` seconds are rejected (pass `0` to disable that check). More than one `v1` signature in the header is handled, so rotating the signing secret does not drop events. The payload **must** be the raw body — a re-encoded array will not match.

### Error handling

Any non-2xx API response throws `JeffersonGoncalves\Stripe\Exceptions\StripeException`, which exposes the decoded error body along with Stripe's code, type and offending parameter:

```php
use JeffersonGoncalves\Stripe\Exceptions\StripeException;

try {
    Stripe::subscriptions()->get('sub_missing');
} catch (StripeException $e) {
    if ($e->errorCode() === 'resource_missing') {
        // ...
    }

    logger()->error($e->getMessage(), $e->errorBody());
}
```

Missing required fields (e.g. `email` on `customers()->create()`) throw `InvalidArgumentException` before any HTTP call is made.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
