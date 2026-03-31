<?php

declare(strict_types=1);

/**
 * Mainlayer PHP SDK — Vendor Example
 *
 * Demonstrates the full vendor workflow:
 *   1. Create a resource
 *   2. Register a webhook to be notified on payments
 *   3. Check revenue analytics
 *   4. Issue a discount coupon
 *   5. Clean up
 *
 * Run: MAINLAYER_API_KEY=ml_xxx php examples/vendor_example.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mainlayer\Exception\MainlayerException;
use Mainlayer\MainlayerClient;

$apiKey = getenv('MAINLAYER_API_KEY') ?: 'ml_your_api_key_here';
$client = new MainlayerClient($apiKey);

try {
    // -------------------------------------------------------------------------
    // 1. Create a resource
    // -------------------------------------------------------------------------
    echo "Creating resource...\n";

    $resource = $client->resources->create([
        'slug'         => 'vendor-demo-sentiment-api',
        'type'         => 'api',
        'price_usdc'   => 0.02,
        'fee_model'    => 'pay_per_call',
        'description'  => 'Real-time sentiment analysis for any text input.',
        'callback_url' => 'https://your-service.example.com/mainlayer/callback',
    ]);

    $resourceId = $resource['id'];
    echo "Resource created: {$resourceId} (slug: {$resource['slug']})\n";

    // -------------------------------------------------------------------------
    // 2. Register a webhook
    // -------------------------------------------------------------------------
    echo "\nRegistering webhook...\n";

    $webhook = $client->webhooks->create([
        'url'    => 'https://your-service.example.com/mainlayer/events',
        'events' => ['payment.completed', 'payment.failed', 'entitlement.granted'],
    ]);

    echo "Webhook registered: {$webhook['id']}\n";

    // -------------------------------------------------------------------------
    // 3. List existing resources
    // -------------------------------------------------------------------------
    echo "\nListing all resources...\n";

    $allResources = $client->resources->list();
    echo count($allResources) . " resource(s) found:\n";
    foreach ($allResources as $r) {
        echo "  - [{$r['id']}] {$r['slug']} @ \${$r['price_usdc']}/call\n";
    }

    // -------------------------------------------------------------------------
    // 4. Revenue analytics
    // -------------------------------------------------------------------------
    echo "\nFetching revenue analytics...\n";

    $analytics = $client->analytics->get();
    echo "Total revenue : \$" . number_format((float) ($analytics['total_revenue'] ?? 0), 4) . "\n";
    echo "Total payments: " . ($analytics['total_payments'] ?? 0) . "\n";

    // -------------------------------------------------------------------------
    // 5. Create a discount coupon
    // -------------------------------------------------------------------------
    echo "\nCreating coupon...\n";

    $coupon = $client->coupons->create([
        'code'         => 'LAUNCH50',
        'discount_pct' => 50,
        'resource_id'  => $resourceId,
        'max_uses'     => 100,
    ]);

    echo "Coupon created: {$coupon['code']} ({$coupon['discount_pct']}% off)\n";

    // -------------------------------------------------------------------------
    // 6. Clean up
    // -------------------------------------------------------------------------
    echo "\nDeleting resource...\n";
    $client->resources->delete($resourceId);
    echo "Resource {$resourceId} deleted.\n";

    echo "\nVendor example completed successfully.\n";

} catch (MainlayerException $e) {
    echo "Mainlayer error [{$e->getStatusCode()}]: {$e->getMessage()}\n";
    exit(1);
}
