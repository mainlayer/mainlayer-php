<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Manages Mainlayer resources (APIs, files, endpoints, and pages).
 *
 * @see https://api.mainlayer.xyz/docs#tag/resources
 */
class ResourcesResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Creates a new resource.
     *
     * @param array{
     *     slug: string,
     *     type: 'api'|'file'|'endpoint'|'page',
     *     price_usdc: float,
     *     fee_model: 'one_time'|'subscription'|'pay_per_call',
     *     description?: string,
     *     callback_url?: string,
     * } $params
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function create(array $params): array
    {
        return $this->http->post('/resources', $params);
    }

    /**
     * Returns a paginated list of resources owned by the authenticated account.
     *
     * @param  array<string, mixed> $query Optional query parameters (e.g. limit, page).
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function list(array $query = []): array
    {
        $response = $this->http->get('/resources', $query);

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }

    /**
     * Retrieves a resource by ID.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function retrieve(string $id): array
    {
        return $this->http->get("/resources/{$id}");
    }

    /**
     * Updates a resource by ID.
     *
     * @param array{
     *     slug?: string,
     *     type?: 'api'|'file'|'endpoint'|'page',
     *     price_usdc?: float,
     *     fee_model?: 'one_time'|'subscription'|'pay_per_call',
     *     description?: string,
     *     callback_url?: string,
     * } $params
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function update(string $id, array $params): array
    {
        return $this->http->patch("/resources/{$id}", $params);
    }

    /**
     * Deletes a resource by ID.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function delete(string $id): array
    {
        return $this->http->delete("/resources/{$id}");
    }

    /**
     * Retrieves a public resource without authentication.
     *
     * Useful for building payment landing pages or displaying resource
     * metadata to unauthenticated users.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function retrievePublic(string $id): array
    {
        return $this->http->get("/resources/public/{$id}");
    }
}
