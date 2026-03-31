<?php

declare(strict_types=1);

/**
 * Mainlayer PHP SDK — Quickstart
 *
 * This script demonstrates the most common operations:
 *   1. Create a resource
 *   2. Process a payment
 *   3. Check access (entitlement)
 *   4. Pull analytics
 *
 * Run: php examples/quickstart.php
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
        'slug'        => 'quickstart-weather-api',
        'type'        => 'api',
        'price_usdc'  => 0.01,
        'fee_model'   => 'pay_per_call',
        'description' => 'Real-time weather data powered by Mainlayer.',
    ]);

    $resourceId = $resource['id'];
    echo "Resource created: {$resourceId}\n";

    // -------------------------------------------------------------------------
    // 2. Process a payment
    // -------------------------------------------------------------------------
    echo "\nProcessing payment...\n";

    $payment = $client->payments->create([
        'resource_id'  => $resourceId,
        'payer_wallet' => 'wallet_demo_abc123',
    ]);

    echo "Payment status: " . ($payment['status'] ?? 'unknown') . "\n";

    // -------------------------------------------------------------------------
    // 3. Check access (entitlement)
    // -------------------------------------------------------------------------
    echo "\nChecking entitlement...\n";

    $entitlement = $client->entitlements->check($resourceId, 'wallet_demo_abc123');

    $hasAccess = $entitlement['has_access'] ? 'YES' : 'NO';
    echo "Has access: {$hasAccess}\n";

    if (isset($entitlement['expires_at'])) {
        echo "Expires at: {$entitlement['expires_at']}\n";
    }

    // -------------------------------------------------------------------------
    // 4. Analytics
    // -------------------------------------------------------------------------
    echo "\nFetching analytics...\n";

    $analytics = $client->analytics->get();
    echo "Analytics: " . json_encode($analytics, JSON_PRETTY_PRINT) . "\n";

} catch (MainlayerException $e) {
    echo "Mainlayer error [{$e->getStatusCode()}]: {$e->getMessage()}\n";
    exit(1);
}
