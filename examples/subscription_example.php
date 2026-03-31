<?php

declare(strict_types=1);

/**
 * Mainlayer PHP SDK — Subscription Example
 *
 * Demonstrates recurring subscription billing:
 *   1. Create a subscription resource
 *   2. Create monthly and annual subscription plans
 *   3. Approve a subscription for a buyer
 *   4. Check entitlement status
 *   5. List active subscriptions
 *   6. Cancel subscription
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
        'slug'        => 'premium-analytics-' . time(),
        'type'        => 'api',
        'price_usdc'  => 0.01,
        'fee_model'   => 'subscription',
        'description' => 'Premium analytics dashboard with monthly billing',
    ]);

    $resourceId = $resource['id'];
    echo "Subscription resource created: {$resourceId}\n\n";

    // -------------------------------------------------------------------------
    // 2. Create subscription plans
    // -------------------------------------------------------------------------
    echo "Creating subscription plans...\n";

    $monthlyPlan = $client->resources->createPlan($resourceId, [
        'interval'       => 'month',
        'interval_count' => 1,
        'price_usdc'     => 29.99,
    ]);
    echo "Monthly Plan created: {$monthlyPlan['id']} (\$29.99/month)\n";

    $annualPlan = $client->resources->createPlan($resourceId, [
        'interval'       => 'year',
        'interval_count' => 1,
        'price_usdc'     => 299.99,
    ]);
    echo "Annual Plan created: {$annualPlan['id']} (\$299.99/year)\n\n";

    // -------------------------------------------------------------------------
    // 3. Approve a subscription for a buyer
    // -------------------------------------------------------------------------
    echo "Approving monthly subscription for buyer...\n";

    $subscription = $client->subscriptions->approve([
        'resource_id'  => $resourceId,
        'plan_id'      => $monthlyPlan['id'],
        'payer_wallet' => $payerWallet,
    ]);

    $subscriptionId = $subscription['id'];
    echo "Subscription approved: {$subscriptionId}\n";
    echo "  Status: {$subscription['status']}\n";
    echo "  Next billing: {$subscription['next_billing_date']}\n\n";

    // -------------------------------------------------------------------------
    // 4. Check entitlement status
    // -------------------------------------------------------------------------
    echo "Checking subscription entitlement...\n";

    $entitlement = $client->entitlements->check($resourceId, $payerWallet);
    echo "Has Access: " . ($entitlement['has_access'] ? 'yes' : 'no') . "\n";
    if (!empty($entitlement['expires_at'])) {
        echo "Expires at: {$entitlement['expires_at']}\n";
    }
    echo "\n";

    // -------------------------------------------------------------------------
    // 5. Retrieve subscription details
    // -------------------------------------------------------------------------
    echo "Subscription details:\n";
    $subDetails = $client->subscriptions->retrieve($subscriptionId);
    echo "  ID: {$subDetails['id']}\n";
    echo "  Resource: {$subDetails['resource_id']}\n";
    echo "  Plan: {$subDetails['plan_id']}\n";
    echo "  Buyer: {$subDetails['payer_wallet']}\n";
    echo "  Created: {$subDetails['created_at']}\n\n";

    // -------------------------------------------------------------------------
    // 6. List all active subscriptions
    // -------------------------------------------------------------------------
    echo "All active subscriptions:\n";
    $subscriptions = $client->subscriptions->list();
    foreach ($subscriptions as $sub) {
        echo "  - {$sub['id']}: {$sub['status']} (renews {$sub['next_billing_date']})\n";
    }
    echo "\n";

    // -------------------------------------------------------------------------
    // 7. Cancel subscription
    // -------------------------------------------------------------------------
    echo "Cancelling subscription...\n";
    $client->subscriptions->cancel($subscriptionId);
    echo "Subscription {$subscriptionId} cancelled!\n";
    echo "Note: Access continues until end of billing period.\n";

    // -------------------------------------------------------------------------
    // 8. Verify cancellation
    // -------------------------------------------------------------------------
    echo "\nVerifying cancellation...\n";
    $cancelled = $client->subscriptions->retrieve($subscriptionId);
    echo "Status: {$cancelled['status']}\n";

    echo "\nSubscription example completed successfully!\n";

} catch (MainlayerException $e) {
    echo "Mainlayer error [{$e->getStatusCode()}]: {$e->getMessage()}\n";
    exit(1);
}
