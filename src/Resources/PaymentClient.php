<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exceptions\MainlayerException;

/**
 * Handles payment operations for the Mainlayer façade client.
 *
 * @internal
 */
class PaymentClient extends BaseResourceClient
{
    /**
     * Initiates a payment for a resource.
     *
     * @param string $resourceId  The ID of the resource to pay for.
     * @param string $payerWallet The payer's wallet identifier.
     * @param float  $amount      The amount to charge.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function create(string $resourceId, string $payerWallet, float $amount): array
    {
        return $this->post('/pay', [
            'resource_id'  => $resourceId,
            'payer_wallet' => $payerWallet,
            'amount'       => $amount,
        ]);
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
        $response = $this->get('/payments', $query);

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }

    /**
     * Retrieves a payment by ID.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function retrieve(string $id): array
    {
        return $this->get("/payments/{$id}");
    }
}
