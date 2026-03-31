<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Creates and lists Mainlayer payments.
 *
 * @see https://api.mainlayer.xyz/docs#tag/payments
 */
class PaymentsResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Initiates a payment for a resource.
     *
     * @param array{
     *     resource_id: string,
     *     payer_wallet: string,
     * } $params
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function create(array $params): array
    {
        return $this->http->post('/pay', $params);
    }

    /**
     * Retrieves a single payment by ID.
     *
     * @param string $id The payment ID
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function retrieve(string $id): array
    {
        return $this->http->get("/payments/{$id}");
    }

    /**
     * Returns a list of all payments for the authenticated account.
     *
     * @param  array<string, mixed> $query Optional query parameters.
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function list(array $query = []): array
    {
        $response = $this->http->get('/payments', $query);

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }
}
