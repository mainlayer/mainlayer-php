# Mainlayer PHP SDK

Official PHP SDK for [Mainlayer](https://mainlayer.fr) — payment infrastructure for AI agents.

[![Packagist Version](https://img.shields.io/packagist/v/mainlayer/mainlayer-php.svg)](https://packagist.org/packages/mainlayer/mainlayer-php)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Documentation](https://img.shields.io/badge/docs-mainlayer.fr-blue.svg)](https://docs.mainlayer.fr)

Monetize AI tools and agents with one API. Accept payments, gate access, and manage subscriptions — without writing any payment infrastructure yourself.

**Features:**
- Create and manage monetizable resources
- Accept one-time and subscription payments
- Check access entitlements in real-time
- Search the public marketplace
- Manage webhooks and analytics
- Automatic retry logic with exponential backoff

## Requirements

- PHP 8.1 or later
- [Composer](https://getcomposer.org/)

## Installation

```bash
composer require mainlayer/mainlayer-php
```

## Quick Start

### Vendor: Create and Monetize a Resource

```php
<?php

require_once 'vendor/autoload.php';

use Mainlayer\MainlayerClient;

$client = new MainlayerClient($_ENV['MAINLAYER_API_KEY']);

// 1. Create a paid resource
$resource = $client->resources->create([
    'slug'        => 'ai-text-summarizer',
    'type'        => 'api',
    'price_usdc'  => 0.05,
    'fee_model'   => 'pay_per_call',
    'description' => 'Summarize any text using GPT-4',
]);

echo "Resource ID: {$resource['id']}\n";

// 2. Activate the resource
$client->resources->activate($resource['id']);

// 3. Get webhook secret
$secret = $client->resources->webhookSecret($resource['id']);
echo "Webhook Secret: {$secret['secret']}\n";
```

### Buyer: Accept Payment and Verify Access

```php
<?php

// Accept a payment
$payment = $client->payments->create([
    'resource_id'  => 'res_abc123',
    'payer_wallet' => 'buyer_wallet_address',
]);

echo "Payment Status: {$payment['status']}\n";

// Verify access
$access = $client->entitlements->check('res_abc123', 'buyer_wallet_address');

if ($access['has_access']) {
    echo "Access granted!\n";
    echo "Expires: {$access['expires_at']}\n";
    echo "Credits: {$access['credits_remaining']}\n";
} else {
    http_response_code(402);
    echo "Payment required\n";
}
```

## Configuration

| Option | Description | Default |
|--------|-------------|---------|
| `$apiKey` | Your Mainlayer API key (required) | — |
| `$options` | Additional Guzzle client options (timeout, verify_ssl, etc.) | `[]` |
| `$guzzle` | Inject a custom Guzzle client (for testing) | `null` |

```php
// Custom Guzzle configuration
$client = new MainlayerClient('ml_...', [
    'timeout' => 30,
    'verify'  => true,  // SSL verification
]);

// Using with staging/sandbox
$client = new MainlayerClient('ml_...', [
    'base_uri' => 'https://staging.api.mainlayer.fr',
]);
```

### Authentication

#### Register

Create a new account.

```php
$result = $client->auth->register([
    'email'    => 'newuser@example.com',
    'password' => 'secure_password_123',
]);

$token = $result['access_token'];
```

#### Login

Exchange email/password for an access token.

```php
$result = $client->auth->login([
    'email'    => 'you@example.com',
    'password' => 'your_password',
]);

$token = $result['access_token'];
```

### Vendors

#### Register with Wallet

Register as a vendor using wallet signature.

```php
$vendor = $client->vendors->register([
    'wallet_address' => '0x742d35Cc...',
    'nonce'          => 'unique_nonce_123',
    'signed_message' => '0x...',
]);

echo "Vendor ID: {$vendor['id']}\n";
echo "Verified: " . ($vendor['verified'] ? 'yes' : 'no') . "\n";
```

## Resources

Manage billable resources (APIs, files, endpoints, pages).

```php
// Create
$resource = $client->resources->create([
    'slug'        => 'my-api',
    'type'        => 'api',          // 'api' | 'file' | 'endpoint' | 'page'
    'price_usdc'  => 0.05,
    'fee_model'   => 'pay_per_call', // 'one_time' | 'subscription' | 'pay_per_call'
    'description' => 'My API',
]);

// List
$resources = $client->resources->list();

// Retrieve
$resource = $client->resources->retrieve('res_abc123');

// Update
$updated = $client->resources->update('res_abc123', ['price_usdc' => 0.03]);

// Delete
$client->resources->delete('res_abc123');

// Public retrieval (no auth required)
$public = $client->resources->retrievePublic('res_abc123');

// Activate
$client->resources->activate('res_abc123');

// Get/Update quota
$quota = $client->resources->quota('res_abc123');
$client->resources->updateQuota('res_abc123', ['available_credits' => 1000]);

// Webhook secret
$secret = $client->resources->webhookSecret('res_abc123');

// Plans (subscriptions)
$plans = $client->resources->plans('res_abc123');
$plan = $client->resources->createPlan('res_abc123', [
    'interval'       => 'month',
    'interval_count' => 1,
    'price_usdc'     => 9.99,
]);
$client->resources->updatePlan('res_abc123', 'plan_id', ['price_usdc' => 11.99]);
$client->resources->deletePlan('res_abc123', 'plan_id');
```

## Payments

```php
// Create a payment
$payment = $client->payments->create([
    'resource_id'  => 'res_abc123',
    'payer_wallet' => 'wallet_buyer_xyz',
]);

// Retrieve payment
$payment = $client->payments->retrieve('payment_abc123');
echo "Status: {$payment['status']}\n";

// List payments
$payments = $client->payments->list();
```

## Subscriptions

```php
// Approve subscription
$subscription = $client->subscriptions->approve([
    'resource_id'  => 'res_abc123',
    'plan_id'      => 'plan_xyz789',
    'payer_wallet' => 'buyer_wallet',
]);

// Cancel subscription
$client->subscriptions->cancel('sub_abc123');

// List subscriptions
$subscriptions = $client->subscriptions->list();

// Retrieve subscription
$sub = $client->subscriptions->retrieve('sub_abc123');
echo "Status: {$sub['status']}\n";
```

## Entitlements

```php
$access = $client->entitlements->check('res_abc123', 'wallet_buyer_xyz');

if ($access['has_access']) {
    echo 'Access granted';
    echo 'Expires: ' . ($access['expires_at'] ?? 'never');
}
```

## Discover

Search the public Mainlayer marketplace.

```php
$results = $client->discover->search([
    'q'         => 'weather api',
    'type'      => 'api',
    'fee_model' => 'pay_per_call',
    'limit'     => 10,
]);
```

## Analytics

```php
$analytics = $client->analytics->get();
echo 'Total revenue: $' . $analytics['total_revenue'];
echo 'Total payments: ' . $analytics['total_payments'];
```

## Webhooks

```php
// Register
$webhook = $client->webhooks->create([
    'url'    => 'https://example.com/webhooks/mainlayer',
    'events' => ['payment.completed', 'payment.failed'],
]);

// List
$webhooks = $client->webhooks->list();

// Delete
$client->webhooks->delete('wh_abc123');
```

## Coupons

```php
// Create
$coupon = $client->coupons->create([
    'code'         => 'LAUNCH50',
    'discount_pct' => 50,
    'resource_id'  => 'res_abc123',
    'max_uses'     => 100,
]);

// List
$coupons = $client->coupons->list();
```

## Error Handling

All API errors throw a `Mainlayer\Exception\MainlayerException` (or a subclass):

| Exception | HTTP Status |
|-----------|-------------|
| `AuthenticationException` | 401 |
| `NotFoundException` | 404 |
| `RateLimitException` | 429 |
| `MainlayerException` | All other errors |

```php
use Mainlayer\Exception\AuthenticationException;
use Mainlayer\Exception\MainlayerException;
use Mainlayer\Exception\NotFoundException;
use Mainlayer\Exception\RateLimitException;

try {
    $resource = $client->resources->retrieve('res_missing');
} catch (NotFoundException $e) {
    echo 'Not found: ' . $e->getMessage();
} catch (AuthenticationException $e) {
    echo 'Auth failed — check your API key.';
} catch (RateLimitException $e) {
    echo 'Rate limited. Retry after a moment.';
} catch (MainlayerException $e) {
    echo 'API error [' . $e->getStatusCode() . ']: ' . $e->getMessage();
    print_r($e->getBody());
}
```

The SDK automatically retries `429` and `5xx` responses up to **3 times** with exponential backoff.

## Facade Client

For a simpler, flat API, use the `Mainlayer\Mainlayer` facade:

```php
use Mainlayer\Mainlayer;

$ml = new Mainlayer('ml_your_api_key');

$resource = $ml->createResource(['slug' => 'my-api', 'type' => 'api', 'price' => 0.01, 'fee_model' => 'pay_per_call']);
$payment  = $ml->pay($resource['id'], 'wallet_abc', 0.01);
$access   = $ml->checkEntitlement($resource['id'], 'wallet_abc');
$results  = $ml->discover('weather api', 10);
$revenue  = $ml->getRevenue();
$vendor   = $ml->getVendor();
```

## Models

Typed model classes for IDE autocomplete and strict typing:

```php
use Mainlayer\Models\Resource;
use Mainlayer\Models\Payment;
use Mainlayer\Models\Entitlement;
use Mainlayer\Models\Vendor;

$resource    = Resource::fromArray($client->resources->retrieve('res_abc'));
$payment     = Payment::fromArray($client->payments->create([...]));
$entitlement = Entitlement::fromArray($client->entitlements->check('res_abc', 'wallet_xyz'));
```

## Examples

Run examples with your API key:

```bash
MAINLAYER_API_KEY=ml_live_... php examples/quickstart.php
MAINLAYER_API_KEY=ml_live_... php examples/vendor_example.php
MAINLAYER_API_KEY=ml_live_... php examples/buyer_example.php
MAINLAYER_API_KEY=ml_live_... php examples/subscription_example.php
```

| File | Description |
|------|-------------|
| `examples/quickstart.php` | Full end-to-end flow: register → create resource → accept payment → verify access |
| `examples/vendor_example.php` | Vendor: create resources, manage plans, webhooks, analytics |
| `examples/buyer_example.php` | Buyer: discover resources, accept payment, check entitlements |
| `examples/subscription_example.php` | Subscriptions: create plans, approve subscriptions, manage renewals |

## Testing

```bash
composer install
composer test
```

Static analysis:

```bash
composer analyse
```

## License

MIT. See [LICENSE](LICENSE) for details.
