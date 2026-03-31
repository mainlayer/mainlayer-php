<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Manages webhook subscriptions for Mainlayer events.
 *
 * @see https://api.mainlayer.xyz/docs#tag/webhooks
 */
class WebhooksResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Registers a new webhook endpoint.
     *
     * @param array{
     *     url: string,
     *     events: array<string>,
     * } $params
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     *
     * @example
     * $webhook = $client->webhooks->create([
     *     'url' => 'https://example.com/webhooks/mainlayer',
     *     'events' => ['payment.completed', 'payment.failed'],
     * ]);
     */
    public function create(array $params): array
    {
        return $this->http->post('/webhooks', $params);
    }

    /**
     * Lists all webhook subscriptions for the authenticated account.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function list(): array
    {
        $response = $this->http->get('/webhooks');

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }

    /**
     * Deletes a webhook subscription by ID.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function delete(string $id): array
    {
        return $this->http->delete("/webhooks/{$id}");
    }
}
