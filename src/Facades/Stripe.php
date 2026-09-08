<?php

namespace JeffersonGoncalves\Stripe\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\Stripe\Stripe
 */
class Stripe extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JeffersonGoncalves\Stripe\Stripe::class;
    }
}
