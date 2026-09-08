<?php

namespace JeffersonGoncalves\Stripe;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class StripeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('stripe')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Stripe::class, function () {
            return new Stripe(
                (string) config('stripe.secret'),
                (string) config('stripe.base_url', 'https://api.stripe.com'),
                (int) config('stripe.default_limit', 10),
                config('stripe.webhook_secret'),
                config('stripe.api_version'),
            );
        });
    }
}
