<?php

declare(strict_types=1);

namespace Mainlayer;

use Mainlayer\Exceptions\MainlayerException;
use Mainlayer\Resources\EntitlementClient;
use Mainlayer\Resources\PaymentClient;
use Mainlayer\Resources\ResourceClient;
use Mainlayer\Resources\VendorClient;

/**
 * Simple façade client for the Mainlayer payment API.
 *
 * Provides a flat, method-based interface for the most common operations.
 * For the full resource-oriented client, see {@see MainlayerClient}.
 *
 * @example
 * $ml = new \Mainlayer\Mainlayer('ml_your_api_key');
 * $resource = $ml->createResource(['slug' => 'my-api', 'type' => 'api', 'price' => 0.01, 'fee_model' => 'pay_per_call']);
 * $payment  = $ml->pay($resource['id'], 'wallet_abc', 0.01);
 * $access   = $ml->checkEntitlement($resource['id'], 'wallet_abc');
 */
class Mainlayer
{
    private string $baseUrl = 'https://api.mainlayer.xyz';

    private ResourceClient $resourceClient;
    private PaymentClient $paymentClient;
    private EntitlementClient $entitlementClient;
    private VendorClient $vendorClient;

    /**
     * Creates a new Mainlayer façade client.
     *
     * @param string $apiKey Your Mainlayer API key (begins with "ml_").
     *
     * @throws \InvalidArgumentException If the API key is empty.
     */
    public function __construct(private readonly string $apiKey)
    {
        if (trim($apiKey) === '') {
            throw new \InvalidArgumentException(
                'A Mainlayer API key is required. Pass your key as the first argument.'
            );
        }

        $this->resourceClient    = new ResourceClient($apiKey, $this->baseUrl);
        $this->paymentClient     = new PaymentClient($apiKey, $this->baseUrl);
        $this->entitlementClient = new EntitlementClient($apiKey, $this->baseUrl);
        $this->vendorClient      = new VendorClient($apiKey, $this->baseUrl);
    }

    // -------------------------------------------------------------------------
    // Resources
    // -------------------------------------------------------------------------

    /**
     * Creates a new billable resource.
     *
     * @param array{
     *     slug: string,
     *     type: 'api'|'file'|'endpoint'|'page',
     *     price: float,
     *     fee_model: 'one_time'|'subscription'|'pay_per_call',
     *     description?: string,
     *     callback_url?: string,
     * } $data
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function createResource(array $data): array
    {
        return $this->resourceClient->create($data);
    }

    /**
     * Returns all resources owned by the authenticated account.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function listResources(): array
    {
        return $this->resourceClient->list();
    }

    /**
     * Retrieves a resource by ID.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function getResource(string $id): array
    {
        return $this->resourceClient->retrieve($id);
    }

    /**
     * Updates a resource by ID.
     *
     * @param array<string, mixed> $data Fields to update.
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function updateResource(string $id, array $data): array
    {
        return $this->resourceClient->update($id, $data);
    }

    /**
     * Deletes a resource by ID.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function deleteResource(string $id): array
    {
        return $this->resourceClient->delete($id);
    }

    // -------------------------------------------------------------------------
    // Payments
    // -------------------------------------------------------------------------

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
    public function pay(string $resourceId, string $payerWallet, float $amount): array
    {
        return $this->paymentClient->create($resourceId, $payerWallet, $amount);
    }

    // -------------------------------------------------------------------------
    // Entitlements
    // -------------------------------------------------------------------------

    /**
     * Checks whether a payer wallet has active access to a resource.
     *
     * @return array{has_access: bool, expires_at: string|null}
     *
     * @throws MainlayerException
     */
    public function checkEntitlement(string $resourceId, string $payerWallet): array
    {
        return $this->entitlementClient->check($resourceId, $payerWallet);
    }

    // -------------------------------------------------------------------------
    // Discovery
    // -------------------------------------------------------------------------

    /**
     * Searches the public Mainlayer marketplace.
     *
     * @param  string $query Search query string.
     * @param  int    $limit Maximum number of results to return (default 20).
     * @return array<int, array<string, mixed>>
     *
     * @throws MainlayerException
     */
    public function discover(string $query = '', int $limit = 20): array
    {
        return $this->resourceClient->discover($query, $limit);
    }

    // -------------------------------------------------------------------------
    // Vendor / Account
    // -------------------------------------------------------------------------

    /**
     * Returns aggregated revenue and usage analytics for the account.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function getRevenue(): array
    {
        return $this->vendorClient->getRevenue();
    }

    /**
     * Returns profile information for the authenticated vendor account.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function getVendor(): array
    {
        return $this->vendorClient->getProfile();
    }
}
