<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exceptions\MainlayerException;

/**
 * Checks access entitlements for the Mainlayer façade client.
 *
 * @internal
 */
class EntitlementClient extends BaseResourceClient
{
    /**
     * Checks whether a payer wallet has active access to a resource.
     *
     * @return array{has_access: bool, expires_at: string|null}
     *
     * @throws MainlayerException
     *
     * @example
     * $access = $client->entitlements->check('res_123', 'wallet_abc');
     * if ($access['has_access']) {
     *     // grant access to protected content
     * }
     */
    public function check(string $resourceId, string $payerWallet): array
    {
        /** @var array{has_access: bool, expires_at: string|null} */
        return $this->get('/entitlements/check', [
            'resource_id'  => $resourceId,
            'payer_wallet' => $payerWallet,
        ]);
    }

    /**
     * Returns all entitlements for the authenticated account.
     *
     * @param  array<string, mixed> $query Optional query parameters.
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function list(array $query = []): array
    {
        $response = $this->get('/entitlements', $query);

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }
}
