<?php

declare(strict_types=1);

namespace Mainlayer\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mainlayer\Exception\AuthenticationException;
use Mainlayer\Exception\MainlayerException;
use Mainlayer\Exception\NotFoundException;
use Mainlayer\Exception\RateLimitException;
use Mainlayer\Exceptions\MainlayerException as FacadeException;
use Mainlayer\MainlayerClient;
use Mainlayer\Models\Entitlement;
use Mainlayer\Models\Payment;
use Mainlayer\Models\Resource;
use Mainlayer\Models\Vendor;
use PHPUnit\Framework\TestCase;

/**
 * Integration-style tests for the Mainlayer PHP SDK.
 *
 * HTTP calls are intercepted via Guzzle's MockHandler — no real network
 * traffic is made. Tests cover happy paths, error paths, retries, and models.
 */
class MainlayerTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Builds a MainlayerClient backed by a mock Guzzle handler.
     *
     * @param Response[] $responses Ordered list of responses to return.
     */
    private function buildClient(array $responses): MainlayerClient
    {
        $mock    = new MockHandler($responses);
        $stack   = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $stack, 'http_errors' => false]);

        return new MainlayerClient('ml_test_key', [], $guzzle);
    }

    private function jsonResponse(array $data, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($data));
    }

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    /** @test */
    public function it_throws_when_api_key_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new MainlayerClient('');
    }

    /** @test */
    public function it_throws_when_api_key_is_whitespace_only(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new MainlayerClient('   ');
    }

    /** @test */
    public function it_constructs_successfully_with_valid_key(): void
    {
        $client = new MainlayerClient('ml_valid_key');
        $this->assertInstanceOf(MainlayerClient::class, $client);
    }

    // -------------------------------------------------------------------------
    // Resources — happy path
    // -------------------------------------------------------------------------

    /** @test */
    public function it_creates_a_resource(): void
    {
        $payload = ['id' => 'res_001', 'slug' => 'my-api', 'type' => 'api', 'price_usdc' => 0.01, 'fee_model' => 'pay_per_call'];
        $client  = $this->buildClient([$this->jsonResponse($payload, 201)]);

        $result = $client->resources->create([
            'slug'      => 'my-api',
            'type'      => 'api',
            'price_usdc' => 0.01,
            'fee_model' => 'pay_per_call',
        ]);

        $this->assertSame('res_001', $result['id']);
        $this->assertSame('my-api', $result['slug']);
    }

    /** @test */
    public function it_lists_resources(): void
    {
        $payload = ['data' => [
            ['id' => 'res_001', 'slug' => 'api-one'],
            ['id' => 'res_002', 'slug' => 'api-two'],
        ]];
        $client = $this->buildClient([$this->jsonResponse($payload)]);

        $list = $client->resources->list();

        $this->assertCount(2, $list);
        $this->assertSame('res_001', $list[0]['id']);
    }

    /** @test */
    public function it_retrieves_a_resource_by_id(): void
    {
        $payload = ['id' => 'res_001', 'slug' => 'my-api'];
        $client  = $this->buildClient([$this->jsonResponse($payload)]);

        $result = $client->resources->retrieve('res_001');

        $this->assertSame('my-api', $result['slug']);
    }

    /** @test */
    public function it_updates_a_resource(): void
    {
        $payload = ['id' => 'res_001', 'slug' => 'my-api', 'price_usdc' => 0.05];
        $client  = $this->buildClient([$this->jsonResponse($payload)]);

        $result = $client->resources->update('res_001', ['price_usdc' => 0.05]);

        $this->assertSame(0.05, $result['price_usdc']);
    }

    /** @test */
    public function it_deletes_a_resource(): void
    {
        $payload = ['deleted' => true, 'id' => 'res_001'];
        $client  = $this->buildClient([$this->jsonResponse($payload)]);

        $result = $client->resources->delete('res_001');

        $this->assertTrue($result['deleted']);
    }

    /** @test */
    public function it_retrieves_a_public_resource_without_auth(): void
    {
        $payload = ['id' => 'res_001', 'slug' => 'my-api', 'public' => true];
        $client  = $this->buildClient([$this->jsonResponse($payload)]);

        $result = $client->resources->retrievePublic('res_001');

        $this->assertTrue($result['public']);
    }

    // -------------------------------------------------------------------------
    // Payments
    // -------------------------------------------------------------------------

    /** @test */
    public function it_creates_a_payment(): void
    {
        $payload = ['id' => 'pay_001', 'status' => 'completed', 'resource_id' => 'res_001'];
        $client  = $this->buildClient([$this->jsonResponse($payload, 201)]);

        $result = $client->payments->create([
            'resource_id'  => 'res_001',
            'payer_wallet' => 'wallet_abc',
        ]);

        $this->assertSame('pay_001', $result['id']);
        $this->assertSame('completed', $result['status']);
    }

    /** @test */
    public function it_lists_payments(): void
    {
        $payload = ['data' => [
            ['id' => 'pay_001', 'status' => 'completed'],
            ['id' => 'pay_002', 'status' => 'pending'],
        ]];
        $client = $this->buildClient([$this->jsonResponse($payload)]);

        $list = $client->payments->list();

        $this->assertCount(2, $list);
    }

    // -------------------------------------------------------------------------
    // Entitlements
    // -------------------------------------------------------------------------

    /** @test */
    public function it_checks_entitlement_when_access_is_granted(): void
    {
        $payload = ['has_access' => true, 'expires_at' => null];
        $client  = $this->buildClient([$this->jsonResponse($payload)]);

        $result = $client->entitlements->check('res_001', 'wallet_abc');

        $this->assertTrue($result['has_access']);
    }

    /** @test */
    public function it_checks_entitlement_when_access_is_denied(): void
    {
        $payload = ['has_access' => false, 'expires_at' => null];
        $client  = $this->buildClient([$this->jsonResponse($payload)]);

        $result = $client->entitlements->check('res_001', 'wallet_xyz');

        $this->assertFalse($result['has_access']);
    }

    // -------------------------------------------------------------------------
    // Discover
    // -------------------------------------------------------------------------

    /** @test */
    public function it_searches_the_marketplace(): void
    {
        $payload = ['data' => [
            ['id' => 'res_010', 'slug' => 'weather-api'],
        ]];
        $client = $this->buildClient([$this->jsonResponse($payload)]);

        $results = $client->discover->search(['q' => 'weather', 'limit' => 10]);

        $this->assertCount(1, $results);
        $this->assertSame('weather-api', $results[0]['slug']);
    }

    // -------------------------------------------------------------------------
    // Analytics
    // -------------------------------------------------------------------------

    /** @test */
    public function it_returns_analytics(): void
    {
        $payload = ['total_revenue' => 12.50, 'total_payments' => 250, 'resources' => []];
        $client  = $this->buildClient([$this->jsonResponse($payload)]);

        $analytics = $client->analytics->get();

        $this->assertSame(12.50, $analytics['total_revenue']);
        $this->assertSame(250, $analytics['total_payments']);
    }

    // -------------------------------------------------------------------------
    // Webhooks
    // -------------------------------------------------------------------------

    /** @test */
    public function it_creates_a_webhook(): void
    {
        $payload = ['id' => 'wh_001', 'url' => 'https://example.com/hook'];
        $client  = $this->buildClient([$this->jsonResponse($payload, 201)]);

        $result = $client->webhooks->create([
            'url'    => 'https://example.com/hook',
            'events' => ['payment.completed'],
        ]);

        $this->assertSame('wh_001', $result['id']);
    }

    /** @test */
    public function it_lists_webhooks(): void
    {
        $payload = ['data' => [['id' => 'wh_001'], ['id' => 'wh_002']]];
        $client  = $this->buildClient([$this->jsonResponse($payload)]);

        $list = $client->webhooks->list();

        $this->assertCount(2, $list);
    }

    /** @test */
    public function it_deletes_a_webhook(): void
    {
        $payload = ['deleted' => true];
        $client  = $this->buildClient([$this->jsonResponse($payload)]);

        $result = $client->webhooks->delete('wh_001');

        $this->assertTrue($result['deleted']);
    }

    // -------------------------------------------------------------------------
    // Error handling
    // -------------------------------------------------------------------------

    /** @test */
    public function it_throws_authentication_exception_on_401(): void
    {
        $this->expectException(AuthenticationException::class);

        $payload = ['message' => 'Invalid API key.'];
        $client  = $this->buildClient([$this->jsonResponse($payload, 401)]);

        $client->resources->list();
    }

    /** @test */
    public function it_throws_not_found_exception_on_404(): void
    {
        $this->expectException(NotFoundException::class);

        $payload = ['message' => 'Resource not found.'];
        $client  = $this->buildClient([$this->jsonResponse($payload, 404)]);

        $client->resources->retrieve('res_does_not_exist');
    }

    /** @test */
    public function it_throws_rate_limit_exception_after_retries_exhausted(): void
    {
        $this->expectException(RateLimitException::class);

        $payload = ['message' => 'Too many requests.'];
        // MockHandler needs one response per retry attempt (MAX_RETRIES=3) + original
        $client = $this->buildClient([
            $this->jsonResponse($payload, 429),
            $this->jsonResponse($payload, 429),
            $this->jsonResponse($payload, 429),
            $this->jsonResponse($payload, 429),
        ]);

        $client->resources->list();
    }

    /** @test */
    public function it_throws_mainlayer_exception_on_500(): void
    {
        $this->expectException(MainlayerException::class);

        $payload = ['message' => 'Internal server error.'];
        $client  = $this->buildClient([
            $this->jsonResponse($payload, 500),
            $this->jsonResponse($payload, 500),
            $this->jsonResponse($payload, 500),
            $this->jsonResponse($payload, 500),
        ]);

        $client->resources->list();
    }

    /** @test */
    public function it_includes_status_code_in_exception(): void
    {
        $payload = ['message' => 'Not found.'];
        $client  = $this->buildClient([$this->jsonResponse($payload, 404)]);

        try {
            $client->resources->retrieve('missing');
            $this->fail('Expected NotFoundException was not thrown.');
        } catch (MainlayerException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    /** @test */
    public function it_includes_body_in_exception(): void
    {
        $payload = ['message' => 'Forbidden.', 'code' => 'FORBIDDEN'];
        $client  = $this->buildClient([$this->jsonResponse($payload, 403)]);

        try {
            $client->resources->list();
            $this->fail('Expected MainlayerException was not thrown.');
        } catch (MainlayerException $e) {
            $this->assertArrayHasKey('code', $e->getBody());
        }
    }

    // -------------------------------------------------------------------------
    // Models
    // -------------------------------------------------------------------------

    /** @test */
    public function resource_model_hydrates_from_array(): void
    {
        $data = [
            'id'           => 'res_001',
            'slug'         => 'my-api',
            'type'         => 'api',
            'price_usdc'   => 0.01,
            'fee_model'    => 'pay_per_call',
            'description'  => 'A great API',
            'callback_url' => 'https://example.com/cb',
            'created_at'   => '2026-01-01T00:00:00Z',
        ];

        $resource = Resource::fromArray($data);

        $this->assertSame('res_001', $resource->id);
        $this->assertSame('my-api', $resource->slug);
        $this->assertSame(0.01, $resource->price);
        $this->assertSame('pay_per_call', $resource->feeModel);
    }

    /** @test */
    public function payment_model_hydrates_from_array(): void
    {
        $data = [
            'id'           => 'pay_001',
            'resource_id'  => 'res_001',
            'payer_wallet' => 'wallet_abc',
            'amount'       => 0.05,
            'status'       => 'completed',
        ];

        $payment = Payment::fromArray($data);

        $this->assertSame('pay_001', $payment->id);
        $this->assertTrue($payment->isCompleted());
        $this->assertFalse($payment->isPending());
    }

    /** @test */
    public function entitlement_model_hydrates_from_array(): void
    {
        $data = [
            'has_access'   => true,
            'resource_id'  => 'res_001',
            'payer_wallet' => 'wallet_abc',
            'expires_at'   => null,
        ];

        $entitlement = Entitlement::fromArray($data);

        $this->assertTrue($entitlement->hasAccess);
        $this->assertTrue($entitlement->isActive());
    }

    /** @test */
    public function entitlement_model_reports_inactive_when_expired(): void
    {
        $data = [
            'has_access'   => true,
            'resource_id'  => 'res_001',
            'payer_wallet' => 'wallet_abc',
            'expires_at'   => '2020-01-01T00:00:00Z', // past date
        ];

        $entitlement = Entitlement::fromArray($data);

        $this->assertFalse($entitlement->isActive());
    }

    /** @test */
    public function vendor_model_hydrates_from_array(): void
    {
        $data = [
            'id'             => 'vnd_001',
            'name'           => 'Acme Corp',
            'email'          => 'billing@acme.com',
            'total_revenue'  => 499.99,
            'total_payments' => 1000,
            'created_at'     => '2025-06-01T00:00:00Z',
        ];

        $vendor = Vendor::fromArray($data);

        $this->assertSame('vnd_001', $vendor->id);
        $this->assertSame(499.99, $vendor->totalRevenue);
        $this->assertSame(1000, $vendor->totalPayments);
    }

    /** @test */
    public function vendor_model_serialises_to_array(): void
    {
        $data   = ['id' => 'vnd_001', 'name' => 'Acme', 'email' => 'a@b.com', 'total_revenue' => 0.0, 'total_payments' => 0];
        $vendor = Vendor::fromArray($data);
        $arr    = $vendor->toArray();

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('name', $arr);
    }

    // -------------------------------------------------------------------------
    // Exceptions namespace alias
    // -------------------------------------------------------------------------

    /** @test */
    public function facade_exception_is_throwable(): void
    {
        $this->expectException(FacadeException::class);

        throw new FacadeException('test error', 422, ['field' => 'slug']);
    }

    /** @test */
    public function facade_exception_exposes_status_code_and_body(): void
    {
        $ex = new FacadeException('bad request', 400, ['field' => 'type']);

        $this->assertSame(400, $ex->getStatusCode());
        $this->assertSame(['field' => 'type'], $ex->getBody());
    }
}
