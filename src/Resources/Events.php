<?php

namespace JeffersonGoncalves\Stripe\Resources;

/**
 * The event log behind webhooks. Stripe keeps events for 30 days, so this is
 * the endpoint to replay from after downtime.
 */
class Events extends Resource
{
    protected string $path = '/v1/events';

    /** @param array<string, mixed> $params */
    public function ofType(string $type, array $params = []): array
    {
        return $this->list(array_merge(['type' => $type], $params));
    }
}
