<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Retrieves invoices for the authenticated account.
 *
 * @see https://api.mainlayer.xyz/docs#tag/invoices
 */
class InvoicesResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Returns a list of all invoices for the authenticated account.
     *
     * @param  array<string, mixed> $query Optional query parameters (e.g. limit, page).
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function list(array $query = []): array
    {
        $response = $this->http->get('/invoices', $query);

        /** @var array<int, array<string, mixed>> */
        return $response['data'] ?? $response;
    }
}
