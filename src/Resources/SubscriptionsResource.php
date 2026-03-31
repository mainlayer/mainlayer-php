<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Subscription management for recurring billing.
 */
class SubscriptionsResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Approve a subscription for a buyer.
     *
     * @param array{
     *     resource_id: string,
     *     plan_id: string,
     *     payer_wallet: string,
     * } $params
     *
     * @return array<string, mixed> The created subscription object
     *
     * @throws MainlayerException
     */
    public function approve(array $params): array
    {
        return $this->http->post('/subscriptions/approve', $params);
    }

    /**
     * Cancel an active subscription.
     *
     * @param string $subscriptionId The subscription ID
     *
     * @return array<string, mixed> Confirmation object
     *
     * @throws MainlayerException
     */
    public function cancel(string $subscriptionId): array
    {
        return $this->http->post('/subscriptions/cancel', [
            'subscription_id' => $subscriptionId,
        ]);
    }

    /**
     * List all subscriptions.
     *
     * @param array<string, mixed> $query Optional query parameters
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function list(array $query = []): array
    {
        $response = $this->http->get('/subscriptions', $query);

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }

    /**
     * Retrieve a single subscription.
     *
     * @param string $subscriptionId The subscription ID
     *
     * @return array<string, mixed> The subscription object
     *
     * @throws MainlayerException
     */
    public function retrieve(string $subscriptionId): array
    {
        return $this->http->get("/subscriptions/{$subscriptionId}");
    }
}
