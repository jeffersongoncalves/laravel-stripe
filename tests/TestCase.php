<?php

namespace Jeffersongoncalves\Stripe\Tests;

use Jeffersongoncalves\Stripe\StripeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            StripeServiceProvider::class,
        ];
    }
}
