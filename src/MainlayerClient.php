<?php

declare(strict_types=1);

namespace Mainlayer;

use GuzzleHttp\Client;
use Mainlayer\Resources\AnalyticsResource;
use Mainlayer\Resources\ApiKeysResource;
use Mainlayer\Resources\CouponsResource;
use Mainlayer\Resources\DiscoverResource;
use Mainlayer\Resources\EntitlementsResource;
use Mainlayer\Resources\InvoicesResource;
use Mainlayer\Resources\PaymentsResource;
use Mainlayer\Resources\ResourcesResource;
use Mainlayer\Resources\WebhooksResource;

/**
 * The main entry point for the Mainlayer PHP SDK.
 *
 * Instantiate this class once with your API key and access all
 * Mainlayer features through its resource properties.
 *
 * @property-read ResourcesResource    $resources    Manage billable resources.
 * @property-read PaymentsResource     $payments     Initiate and inspect payments.
 * @property-read EntitlementsResource $entitlements Check access for payer wallets.
 * @property-read DiscoverResource     $discover     Search the public marketplace.
 * @property-read AnalyticsResource    $analytics    View revenue and usage analytics.
 * @property-read WebhooksResource     $webhooks     Manage webhook subscriptions.
 * @property-read CouponsResource      $coupons      Create and list discount coupons.
 * @property-read InvoicesResource     $invoices     Retrieve account invoices.
 * @property-read ApiKeysResource      $apiKeys      Manage API keys.
 *
 * @example
 * $client = new \Mainlayer\MainlayerClient('ml_your_api_key');
 *
 * $resource = $client->resources->create([
 *     'slug'        => 'my-weather-api',
 *     'type'        => 'api',
 *     'price_usdc'  => 0.01,
 *     'fee_model'   => 'pay_per_call',
 *     'description' => 'Real-time weather data for any location.',
 * ]);
 */
class MainlayerClient
{
    public readonly ResourcesResource $resources;
    public readonly PaymentsResource $payments;
    public readonly EntitlementsResource $entitlements;
    public readonly DiscoverResource $discover;
    public readonly AnalyticsResource $analytics;
    public readonly WebhooksResource $webhooks;
    public readonly CouponsResource $coupons;
    public readonly InvoicesResource $invoices;
    public readonly ApiKeysResource $apiKeys;

    private readonly HttpClient $http;

    /**
     * Creates a new Mainlayer client.
     *
     * @param string               $apiKey  Your Mainlayer API key (begins with "ml_").
     * @param array<string, mixed> $options Additional Guzzle client options, or a
     *                                      pre-configured Guzzle client instance.
     * @param Client|null          $guzzle  Inject a custom Guzzle client (useful for testing).
     *
     * @throws \InvalidArgumentException If the API key is empty.
     */
    public function __construct(
        string $apiKey,
        array $options = [],
        ?Client $guzzle = null,
    ) {
        if (trim($apiKey) === '') {
            throw new \InvalidArgumentException(
                'A Mainlayer API key is required. Pass your key as the first argument.'
            );
        }

        $this->http = new HttpClient($apiKey, $options, $guzzle);

        $this->resources = new ResourcesResource($this->http);
        $this->payments = new PaymentsResource($this->http);
        $this->entitlements = new EntitlementsResource($this->http);
        $this->discover = new DiscoverResource($this->http);
        $this->analytics = new AnalyticsResource($this->http);
        $this->webhooks = new WebhooksResource($this->http);
        $this->coupons = new CouponsResource($this->http);
        $this->invoices = new InvoicesResource($this->http);
        $this->apiKeys = new ApiKeysResource($this->http);
    }
}
