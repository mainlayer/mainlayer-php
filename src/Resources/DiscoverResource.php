<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Searches the public Mainlayer resource marketplace.
 *
 * @see https://api.mainlayer.xyz/docs#tag/discover
 */
class DiscoverResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Searches the Mainlayer marketplace for publicly listed resources.
     *
     * @param array{
     *     q?: string,
     *     type?: 'api'|'file'|'endpoint'|'page',
     *     fee_model?: 'one_time'|'subscription'|'pay_per_call',
     *     limit?: int,
     * } $params
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     *
     * @example
     * $results = $client->discover->search(['q' => 'weather api', 'limit' => 10]);
     */
    public function search(array $params = []): array
    {
        $response = $this->http->get('/discover', $params);

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }
}
