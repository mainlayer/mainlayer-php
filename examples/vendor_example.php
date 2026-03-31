<?php

declare(strict_types=1);

/**
 * Mainlayer PHP SDK — Vendor Example
 *
 * Demonstrates the complete vendor workflow:
 *   1. Register vendor (optional)
 *   2. Create a monetizable resource
 *   3. Activate the resource
 *   4. Create subscription plans
 *   5. Get webhook secret
 *   6. View analytics
 *   7. List resources and plans
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
    // 1. Register vendor (optional)
    // -------------------------------------------------------------------------
    echo "Registering vendor with wallet...\n";
    $vendor = $client->vendors->register([
        'wallet_address' => '0x742d35Cc6634C0532925a3b844Bc9e7595f42bE',
        'nonce'          => 'nonce_' . time(),
        'signed_message' => '0xSignedMessageExample',
    ]);
    echo "Vendor ID: {$vendor['id']}\n\n";

    // -------------------------------------------------------------------------
    // 2. Create a resource
    // -------------------------------------------------------------------------
    echo "Creating monetizable resource...\n";

    $resource = $client->resources->create([
        'slug'        => 'ai-summarizer-' . time(),
        'type'        => 'api',
        'price_usdc'  => 0.05,
        'fee_model'   => 'pay_per_call',
        'description' => 'Summarize any text using GPT-4',
    ]);

    $resourceId = $resource['id'];
    echo "Resource created: {$resourceId}\n";
    echo "  Slug: {$resource['slug']}\n";
    echo "  Price: \${$resource['price_usdc']} per call\n\n";

    // -------------------------------------------------------------------------
    // 3. Activate the resource
    // -------------------------------------------------------------------------
    echo "Activating resource...\n";
    $client->resources->activate($resourceId);
    echo "Resource activated!\n\n";

    // -------------------------------------------------------------------------
    // 4. Create subscription plans
    // -------------------------------------------------------------------------
    echo "Creating subscription plans...\n";

    $monthlyPlan = $client->resources->createPlan($resourceId, [
        'interval'       => 'month',
        'interval_count' => 1,
        'price_usdc'     => 9.99,
    ]);
    echo "Monthly Plan created: {$monthlyPlan['id']} (\$9.99/month)\n";

    $annualPlan = $client->resources->createPlan($resourceId, [
        'interval'       => 'year',
        'interval_count' => 1,
        'price_usdc'     => 99.99,
    ]);
    echo "Annual Plan created: {$annualPlan['id']} (\$99.99/year)\n\n";

    // -------------------------------------------------------------------------
    // 5. Get webhook secret
    // -------------------------------------------------------------------------
    echo "Getting webhook secret...\n";
    $secret = $client->resources->webhookSecret($resourceId);
    echo "Webhook Secret: {$secret['secret']} (store securely!)\n\n";

    // -------------------------------------------------------------------------
    // 6. View analytics
    // -------------------------------------------------------------------------
    echo "Fetching analytics...\n";
    $analytics = $client->analytics->get();
    echo "Total Revenue: \${$analytics['total_revenue_usdc'] ?? '0.00'}\n";
    echo "Total Payments: " . ($analytics['total_payments'] ?? 0) . "\n\n";

    // -------------------------------------------------------------------------
    // 7. List all resources
    // -------------------------------------------------------------------------
    echo "Your resources:\n";
    $allResources = $client->resources->list();
    foreach ($allResources as $r) {
        echo "  - {$r['slug']}: \${$r['price_usdc']} ({$r['fee_model']})\n";
    }
    echo "\n";

    // -------------------------------------------------------------------------
    // 8. List all plans for this resource
    // -------------------------------------------------------------------------
    echo "Subscription plans for {$resourceId}:\n";
    $plans = $client->resources->plans($resourceId);
    foreach ($plans as $plan) {
        echo "  - {$plan['id']}: \${$plan['price_usdc']}/{$plan['interval']}\n";
    }

    echo "\nVendor example completed successfully!\n";

} catch (MainlayerException $e) {
    echo "Mainlayer error [{$e->getStatusCode()}]: {$e->getMessage()}\n";
    exit(1);
}
