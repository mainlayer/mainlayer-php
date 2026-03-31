<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Retrieves analytics data for the authenticated account.
 *
 * @see https://api.mainlayer.xyz/docs#tag/analytics
 */
class AnalyticsResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Returns aggregated analytics for all resources owned by the account.
     *
     * The response includes revenue totals, payment counts, top-performing
     * resources, and usage trends.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function get(): array
    {
        return $this->http->get('/analytics');
    }
}
