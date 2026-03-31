<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Manages discount coupons for Mainlayer resources.
 *
 * @see https://api.mainlayer.xyz/docs#tag/coupons
 */
class CouponsResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Creates a new coupon.
     *
     * @param array<string, mixed> $params Coupon configuration.
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function create(array $params): array
    {
        return $this->http->post('/coupons', $params);
    }

    /**
     * Lists all coupons for the authenticated account.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function list(): array
    {
        $response = $this->http->get('/coupons');

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }
}
