<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Manages Mainlayer API keys.
 *
 * @see https://api.mainlayer.xyz/docs#tag/api-keys
 */
class ApiKeysResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Creates a new API key.
     *
     * @param  array{name: string} $params
     * @return array{key: string, id: string, name: string}
     *
     * @throws MainlayerException
     */
    public function create(array $params): array
    {
        /** @var array{key: string, id: string, name: string} */
        return $this->http->post('/api-keys', $params);
    }

    /**
     * Returns a list of all API keys for the authenticated account.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function list(): array
    {
        $response = $this->http->get('/api-keys');

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }

    /**
     * Deletes an API key by ID.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function delete(string $id): array
    {
        return $this->http->delete("/api-keys/{$id}");
    }
}
