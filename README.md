# Mainlayer PHP SDK

Official PHP SDK for [Mainlayer](https://mainlayer.fr) — payment infrastructure for AI agents.

## Requirements

- PHP 8.1 or later
- [Composer](https://getcomposer.org/)

## Installation

```bash
composer require mainlayer/mainlayer-php
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Mainlayer\MainlayerClient;

$client = new MainlayerClient('ml_your_api_key');

// Create a billable resource
$resource = $client->resources->create([
    'slug'      => 'my-weather-api',
    'type'      => 'api',
    'price_usdc' => 0.01,
    'fee_model' => 'pay_per_call',
    'description' => 'Real-time weather data for any location.',
]);

// Process a payment
$payment = $client->payments->create([
    'resource_id'  => $resource['id'],
    'payer_wallet' => 'wallet_buyer_abc',
]);

// Check access
$entitlement = $client->entitlements->check($resource['id'], 'wallet_buyer_abc');
if ($entitlement['has_access']) {
    // Grant access to protected content
}
```

## Configuration

| Option | Description | Default |
|--------|-------------|---------|
| `$apiKey` | Your Mainlayer API key (required) | — |
| `$options` | Additional Guzzle client options | `[]` |
| `$guzzle` | Inject a custom Guzzle client (for testing) | `null` |

## Resources

Manage billable resources (APIs, files, endpoints, pages).

```php
// Create
$resource = $client->resources->create([
    'slug'      => 'my-api',
    'type'      => 'api',          // 'api' | 'file' | 'endpoint' | 'page'
    'price_usdc' => 0.05,
    'fee_model' => 'pay_per_call', // 'one_time' | 'subscription' | 'pay_per_call'
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
```

## Payments

```php
// Create a payment
$payment = $client->payments->create([
    'resource_id'  => 'res_abc123',
    'payer_wallet' => 'wallet_buyer_xyz',
]);

// List payments
$payments = $client->payments->list();
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

| File | Description |
|------|-------------|
| `examples/quickstart.php` | Full end-to-end flow in one file |
| `examples/create_resource.php` | Resource CRUD operations |
| `examples/vendor_example.php` | Vendor: create resources, webhooks, analytics |
| `examples/buyer_example.php` | Buyer: discover, pay, verify access |
| `examples/subscription_example.php` | Recurring subscription billing |

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
