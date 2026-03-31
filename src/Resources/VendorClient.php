<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exceptions\MainlayerException;

/**
 * Retrieves vendor profile and revenue data for the Mainlayer façade client.
 *
 * @internal
 */
class VendorClient extends BaseResourceClient
{
    /**
     * Returns aggregated revenue and usage analytics for the authenticated account.
     *
     * The response includes total revenue, payment counts, top-performing
     * resources, and usage trends over time.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function getRevenue(): array
    {
        return $this->get('/analytics');
    }

    /**
     * Returns vendor profile information for the authenticated account.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function getProfile(): array
    {
        return $this->get('/vendor/profile');
    }

    /**
     * Returns a paginated list of invoices for the authenticated account.
     *
     * @param  array<string, mixed> $query Optional query parameters (e.g. limit, page).
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function listInvoices(array $query = []): array
    {
        $response = $this->get('/invoices', $query);

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }
}
