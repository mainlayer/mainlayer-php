<?php

declare(strict_types=1);

/**
 * Mainlayer PHP SDK — Buyer Example
 *
 * Demonstrates the buyer (payer) workflow:
 *   1. Browse the marketplace for available resources
 *   2. Pay for a resource
 *   3. Verify access via entitlement check
 *   4. Access the protected resource
 *
 * Run: MAINLAYER_API_KEY=ml_xxx php examples/buyer_example.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mainlayer\Exception\MainlayerException;
use Mainlayer\MainlayerClient;

$apiKey      = getenv('MAINLAYER_API_KEY') ?: 'ml_your_api_key_here';
$payerWallet = getenv('PAYER_WALLET')      ?: 'wallet_demo_buyer_001';

$client = new MainlayerClient($apiKey);

try {
    // -------------------------------------------------------------------------
    // 1. Discover resources on the marketplace
    // -------------------------------------------------------------------------
    echo "Searching marketplace for 'weather' APIs...\n";

    $results = $client->discover->search([
        'q'     => 'weather',
        'type'  => 'api',
        'limit' => 5,
    ]);

    if (empty($results)) {
        echo "No marketplace results found for 'weather'. Using demo resource ID.\n";
        $resourceId = 'res_demo_weather_001';
    } else {
        echo count($results) . " result(s) found:\n";
        foreach ($results as $r) {
            echo "  - [{$r['id']}] {$r['slug']} — \${$r['price_usdc']}/call\n";
        }
        $resourceId = $results[0]['id'];
    }

    // -------------------------------------------------------------------------
    // 2. Check current entitlement before paying
    // -------------------------------------------------------------------------
    echo "\nChecking existing entitlement for wallet {$payerWallet}...\n";

    $entitlement = $client->entitlements->check($resourceId, $payerWallet);

    if ($entitlement['has_access']) {
        $expiry = $entitlement['expires_at'] ?? 'never';
        echo "Already have access! Expires: {$expiry}\n";
    } else {
        echo "No active access. Proceeding to payment...\n";

        // ---------------------------------------------------------------------
        // 3. Pay for the resource
        // ---------------------------------------------------------------------
        echo "\nProcessing payment...\n";

        $payment = $client->payments->create([
            'resource_id'  => $resourceId,
            'payer_wallet' => $payerWallet,
        ]);

        $status = $payment['status'] ?? 'unknown';
        echo "Payment {$payment['id']}: {$status}\n";

        if ($status !== 'completed') {
            echo "Payment did not complete. Exiting.\n";
            exit(1);
        }

        // ---------------------------------------------------------------------
        // 4. Confirm access was granted
        // ---------------------------------------------------------------------
        echo "\nVerifying entitlement...\n";

        $check = $client->entitlements->check($resourceId, $payerWallet);
        $hasAccess = $check['has_access'] ? 'YES' : 'NO';
        echo "Access granted: {$hasAccess}\n";

        if (isset($check['expires_at']) && $check['expires_at'] !== null) {
            echo "Access expires: {$check['expires_at']}\n";
        }
    }

    // -------------------------------------------------------------------------
    // 5. Access the protected resource (simulated)
    // -------------------------------------------------------------------------
    echo "\nAccessing protected resource...\n";
    echo "(In production, pass the payer_wallet in your API request header)\n";
    echo "Resource ID: {$resourceId}\n";
    echo "Wallet: {$payerWallet}\n";

    echo "\nBuyer example completed successfully.\n";

} catch (MainlayerException $e) {
    echo "Mainlayer error [{$e->getStatusCode()}]: {$e->getMessage()}\n";
    exit(1);
}
