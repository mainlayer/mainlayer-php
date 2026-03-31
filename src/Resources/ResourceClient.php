<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exceptions\MainlayerException;

/**
 * Handles resource CRUD and marketplace discovery for the Mainlayer façade client.
 *
 * This client is used internally by {@see \Mainlayer\Mainlayer}. For the full
 * resource-oriented API, see {@see ResourcesResource}.
 *
 * @internal
 */
class ResourceClient extends BaseResourceClient
{
    /**
     * Creates a new billable resource.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function create(array $params): array
    {
        return $this->post('/resources', $params);
    }

    /**
     * Returns all resources owned by the authenticated account.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function list(): array
    {
        $response = $this->get('/resources');

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
        return $this->get("/resources/{$id}");
    }

    /**
     * Updates a resource by ID.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function update(string $id, array $params): array
    {
        return $this->patch("/resources/{$id}", $params);
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
        return $this->request('DELETE', "/resources/{$id}");
    }

    /**
     * Searches the public Mainlayer marketplace.
     *
     * @param  string $query Search query string.
     * @param  int    $limit Maximum results (default 20).
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function discover(string $query = '', int $limit = 20): array
    {
        $params = ['limit' => $limit];
        if ($query !== '') {
            $params['q'] = $query;
        }

        $response = $this->get('/discover', $params);

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }
}
