<?php

namespace Jeffersongoncalves\Stripe;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class StripeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-stripe')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
