<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe Secret Key
    |--------------------------------------------------------------------------
    |
    | Server-side key from Developers > API keys. Test keys start with sk_test_
    | and live keys with sk_live_ — never expose either to the browser.
    |
    */
    'secret' => env('STRIPE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Publishable Key
    |--------------------------------------------------------------------------
    |
    | Safe to render client-side (Stripe.js, Elements). Unused by this package,
    | kept here so both keys live in one place.
    |
    */
    'key' => env('STRIPE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Stripe API URL
    |--------------------------------------------------------------------------
    |
    | Override only to point at a proxy or a local mock (e.g. stripe-mock).
    |
    */
    'base_url' => env('STRIPE_BASE_URL', 'https://api.stripe.com'),

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | Sent as the "Stripe-Version" header to pin responses to one API version.
    | Leave null to use whatever version the account defaults to.
    |
    */
    'api_version' => env('STRIPE_API_VERSION'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Signing Secret
    |--------------------------------------------------------------------------
    |
    | Shown when you add an endpoint under Developers > Webhooks (whsec_...).
    | Required only to verify incoming webhook signatures.
    |
    */
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default Pagination Size
    |--------------------------------------------------------------------------
    |
    | Default "limit" for list endpoints when none is given. Stripe caps it
    | at 100.
    |
    */
    'default_limit' => env('STRIPE_DEFAULT_LIMIT', 10),

];
