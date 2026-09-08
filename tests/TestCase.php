<?php

namespace JeffersonGoncalves\Stripe\Tests;

use JeffersonGoncalves\Stripe\StripeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            StripeServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('stripe.secret', 'sk_test_key');
        $app['config']->set('stripe.base_url', 'https://api.stripe.com');
        $app['config']->set('stripe.webhook_secret', 'whsec_test');
    }
}
