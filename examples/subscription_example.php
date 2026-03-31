<?php

declare(strict_types=1);

/**
 * Mainlayer PHP SDK — Subscription Example
 *
 * Demonstrates recurring subscription billing:
 *   1. Create a subscription resource
 *   2. Process a subscription payment
 *   3. Check entitlement expiry
 *   4. Handle renewal
 *   5. Cancel (delete resource)
 *
 * Run: MAINLAYER_API_KEY=ml_xxx php examples/subscription_example.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mainlayer\Exception\MainlayerException;
use Mainlayer\MainlayerClient;
use Mainlayer\Models\Entitlement;

$apiKey      = getenv('MAINLAYER_API_KEY') ?: 'ml_your_api_key_here';
$payerWallet = getenv('PAYER_WALLET')      ?: 'wallet_demo_subscriber_001';

$client = new MainlayerClient($apiKey);

try {
    // -------------------------------------------------------------------------
    // 1. Create a subscription resource
    // -------------------------------------------------------------------------
    echo "Creating subscription resource...\n";

    $resource = $client->resources->create([
        'slug'         => 'pro-data-feed-monthly',
        'type'         => 'api',
        'price_usdc'   => 9.99,
        'fee_model'    => 'subscription',
        'description'  => 'Professional real-time data feed — billed monthly.',
        'callback_url' => 'https://your-service.example.com/mainlayer/subscription',
    ]);

    $resourceId = $resource['id'];
    echo "Subscription resource created: {$resourceId}\n";
    echo "Plan: {$resource['slug']} @ \${$resource['price_usdc']}/month\n";

    // -------------------------------------------------------------------------
    // 2. Process initial subscription payment
    // -------------------------------------------------------------------------
    echo "\nProcessing initial subscription payment...\n";

    $payment = $client->payments->create([
        'resource_id'  => $resourceId,
        'payer_wallet' => $payerWallet,
    ]);

    echo "Payment {$payment['id']}: {$payment['status']}\n";

    // -------------------------------------------------------------------------
    // 3. Check entitlement and expiry
    // -------------------------------------------------------------------------
    echo "\nChecking subscription entitlement...\n";

    $entitlementData = $client->entitlements->check($resourceId, $payerWallet);
    $entitlement     = Entitlement::fromArray(array_merge($entitlementData, [
        'resource_id'  => $resourceId,
        'payer_wallet' => $payerWallet,
    ]));

    if ($entitlement->hasAccess) {
        echo "Active subscription: YES\n";

        if ($entitlement->expiresAt !== null) {
            echo "Renews before: {$entitlement->expiresAt}\n";

            $daysUntilRenewal = (int) ceil((strtotime($entitlement->expiresAt) - time()) / 86400);
            echo "Days until renewal: {$daysUntilRenewal}\n";

            if ($daysUntilRenewal <= 3) {
                echo "\nRenewal due soon — processing renewal payment...\n";

                $renewal = $client->payments->create([
                    'resource_id'  => $resourceId,
                    'payer_wallet' => $payerWallet,
                ]);

                echo "Renewal payment {$renewal['id']}: {$renewal['status']}\n";
            }
        } else {
            echo "Subscription type: lifetime access\n";
        }
    } else {
        echo "No active subscription found.\n";
    }

    // -------------------------------------------------------------------------
    // 4. List all payments for this subscription
    // -------------------------------------------------------------------------
    echo "\nPayment history:\n";

    $payments = $client->payments->list(['resource_id' => $resourceId]);
    if (empty($payments)) {
        echo "  No payment history found.\n";
    } else {
        foreach ($payments as $p) {
            $date = $p['created_at'] ?? 'unknown date';
            echo "  - {$p['id']} | {$p['status']} | \${$p['amount']} | {$date}\n";
        }
    }

    // -------------------------------------------------------------------------
    // 5. Cancel subscription (delete resource)
    // -------------------------------------------------------------------------
    echo "\nCancelling subscription (deleting resource)...\n";
    $client->resources->delete($resourceId);
    echo "Subscription {$resourceId} cancelled.\n";

    echo "\nSubscription example completed successfully.\n";

} catch (MainlayerException $e) {
    echo "Mainlayer error [{$e->getStatusCode()}]: {$e->getMessage()}\n";
    exit(1);
}
