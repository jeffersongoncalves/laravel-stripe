<?php

namespace Jeffersongoncalves\Stripe\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jeffersongoncalves\Stripe\Stripe
 */
class Stripe extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-stripe';
    }
}
