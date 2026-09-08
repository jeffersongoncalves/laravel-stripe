<?php

use Illuminate\Http\Client\Response;
use JeffersonGoncalves\Stripe\Exceptions\StripeException;

function fakeStripeResponse(int $status, array $body): Response
{
    return new Response(new GuzzleHttp\Psr7\Response($status, [], json_encode($body)));
}

it('builds the message from the Stripe error message', function () {
    $exception = StripeException::fromResponse(fakeStripeResponse(404, [
        'error' => [
            'type' => 'invalid_request_error',
            'code' => 'resource_missing',
            'message' => 'No such customer: cus_missing',
            'param' => 'customer',
        ],
    ]));

    expect($exception->getMessage())->toBe('No such customer: cus_missing')
        ->and($exception->getCode())->toBe(404)
        ->and($exception->errorCode())->toBe('resource_missing')
        ->and($exception->errorType())->toBe('invalid_request_error')
        ->and($exception->param())->toBe('customer');
});

it('falls back to the error code when there is no message', function () {
    $exception = StripeException::fromResponse(fakeStripeResponse(402, [
        'error' => ['code' => 'card_declined'],
    ]));

    expect($exception->getMessage())->toBe('card_declined');
});

it('falls back to a generic message on an empty body', function () {
    $exception = StripeException::fromResponse(fakeStripeResponse(500, []));

    expect($exception->getMessage())->toBe('Stripe API error (HTTP 500).')
        ->and($exception->errorCode())->toBeNull()
        ->and($exception->errorType())->toBeNull()
        ->and($exception->param())->toBeNull();
});
