<?php

declare(strict_types=1);

/**
 * Mainlayer PHP SDK — Create a Resource
 *
 * Demonstrates creating, retrieving, updating, and deleting a resource.
 *
 * Run: php examples/create_resource.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mainlayer\Exception\MainlayerException;
use Mainlayer\MainlayerClient;

$apiKey = getenv('MAINLAYER_API_KEY') ?: 'ml_your_api_key_here';

$client = new MainlayerClient($apiKey);

try {
    // Create
    echo "Creating resource...\n";
    $resource = $client->resources->create([
        'slug'         => 'my-sentiment-api',
        'type'         => 'api',
        'price_usdc'   => 0.05,
        'fee_model'    => 'pay_per_call',
        'description'  => 'Sentiment analysis for any text, powered by Mainlayer.',
        'callback_url' => 'https://example.com/callbacks/mainlayer',
    ]);

    $id = $resource['id'];
    echo "Created: {$id}\n";

    // Retrieve
    echo "\nRetrieving resource...\n";
    $fetched = $client->resources->retrieve($id);
    echo "Slug: {$fetched['slug']}\n";
    echo "Price: \${$fetched['price_usdc']} USDC\n";

    // Update
    echo "\nUpdating price...\n";
    $updated = $client->resources->update($id, ['price_usdc' => 0.03]);
    echo "New price: \${$updated['price_usdc']} USDC\n";

    // List
    echo "\nListing all resources...\n";
    $all = $client->resources->list();
    echo count($all) . " resource(s) found.\n";

    // Public retrieval (no auth required)
    echo "\nPublic resource lookup...\n";
    $public = $client->resources->retrievePublic($id);
    echo "Public slug: {$public['slug']}\n";

    // Delete
    echo "\nDeleting resource...\n";
    $client->resources->delete($id);
    echo "Resource {$id} deleted.\n";

} catch (MainlayerException $e) {
    echo "Mainlayer error [{$e->getStatusCode()}]: {$e->getMessage()}\n";
    exit(1);
}
