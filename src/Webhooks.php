<?php

namespace JeffersonGoncalves\Stripe;

use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Verification of Stripe's "Stripe-Signature" webhook header.
 *
 * The header looks like "t=1492774577,v1=5257a869e7...,v1=...", and the signed
 * payload is "<t>.<raw request body>" hashed with HMAC-SHA256 using the
 * endpoint's signing secret (whsec_...). More than one v1 signature can be
 * present while a secret is being rotated — any match counts.
 */
class Webhooks
{
    public function __construct(
        protected ?string $secret = null,
    ) {}

    /**
     * @param  string  $payload  The RAW request body — a re-encoded array will not match.
     * @param  int  $tolerance  Seconds of clock skew tolerated; 0 disables the check.
     */
    public function verify(string $payload, string $signature, int $tolerance = 300): bool
    {
        if ($this->secret === null || $this->secret === '') {
            throw new InvalidArgumentException('No Stripe webhook secret configured. Set STRIPE_WEBHOOK_SECRET.');
        }

        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $signature) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);

            if ($value === null) {
                continue;
            }

            match (trim($key)) {
                't' => $timestamp = $value,
                'v1' => $signatures[] = $value,
                default => null,
            };
        }

        if ($timestamp === null || $signatures === []) {
            return false;
        }

        if ($tolerance > 0 && abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $this->secret);

        foreach ($signatures as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }

    public function verifyRequest(Request $request, int $tolerance = 300): bool
    {
        return $this->verify(
            $request->getContent(),
            (string) $request->header('Stripe-Signature'),
            $tolerance,
        );
    }
}
