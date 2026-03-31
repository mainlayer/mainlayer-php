<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Vendor registration and management operations.
 */
class VendorsResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Register a new vendor with wallet signature authentication.
     *
     * @param array{
     *     wallet_address: string,
     *     nonce: string,
     *     signed_message: string,
     * } $params
     *
     * @return array<string, mixed> The registered vendor object
     *
     * @throws MainlayerException
     */
    public function register(array $params): array
    {
        return $this->http->post('/vendors/register', $params);
    }
}
