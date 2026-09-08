<?php

namespace JeffersonGoncalves\Stripe\Resources;

use InvalidArgumentException;

class Products extends Resource
{
    protected string $path = '/v1/products';

    /** @param array<string, mixed> $attributes */
    public function create(string $name, array $attributes = []): array
    {
        if ($name === '') {
            throw new InvalidArgumentException('The "name" attribute is required.');
        }

        return $this->client->post($this->path, array_merge(['name' => $name], $attributes));
    }

    /** @param array<string, mixed> $attributes */
    public function update(string $id, array $attributes): array
    {
        return $this->client->post($this->path.'/'.$id, $attributes);
    }

    /** Only products with no price can be deleted; archive the rest. */
    public function delete(string $id): array
    {
        return $this->client->delete($this->path.'/'.$id);
    }

    public function archive(string $id): array
    {
        return $this->update($id, ['active' => 'false']);
    }
}
