<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Checks whether a payer wallet has access to a resource.
 *
 * @see https://api.mainlayer.xyz/docs#tag/entitlements
 */
class EntitlementsResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Checks access for a given resource and payer wallet.
     *
     * @return array{has_access: bool, expires_at: string|null}
     *
     * @throws MainlayerException
     *
     * @example
     * $access = $client->entitlements->check('res_123', 'wallet_abc');
     * if ($access['has_access']) {
     *     // grant access
     * }
     */
    public function check(string $resourceId, string $payerWallet): array
    {
        /** @var array{has_access: bool, expires_at: string|null} */
        return $this->http->get('/entitlements/check', [
            'resource_id' => $resourceId,
            'payer_wallet' => $payerWallet,
        ]);
    }
}
