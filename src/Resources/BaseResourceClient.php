<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exceptions\MainlayerException;

/**
 * Lightweight HTTP base for the façade resource clients.
 *
 * Uses the Guzzle client already pulled in as a composer dependency so the
 * façade clients benefit from the same retry/error handling as the full client.
 *
 * @internal
 */
abstract class BaseResourceClient
{
    private const SDK_VERSION = '1.0.0';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.mainlayer.xyz',
    ) {}

    /**
     * Performs a GET request.
     *
     * @param  array<string, mixed> $query
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    protected function get(string $path, array $query = []): array
    {
        $url = $query !== [] ? $path . '?' . http_build_query($query) : $path;
        return $this->request('GET', $url);
    }

    /**
     * Performs a POST request.
     *
     * @param  array<string, mixed> $body
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    protected function post(string $path, array $body = []): array
    {
        return $this->request('POST', $path, $body);
    }

    /**
     * Performs a PATCH request.
     *
     * @param  array<string, mixed> $body
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    protected function patch(string $path, array $body = []): array
    {
        return $this->request('PATCH', $path, $body);
    }

    /**
     * Executes an HTTP request using curl and returns the decoded response body.
     *
     * @param  array<string, mixed>|null $body
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    protected function request(string $method, string $path, ?array $body = null): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: mainlayer-php/' . self::SDK_VERSION . ' PHP/' . PHP_VERSION,
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);

        if ($body !== null && in_array($method, ['POST', 'PATCH', 'PUT'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response   = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError  = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new MainlayerException("Connection error: {$curlError}", 0, []);
        }

        /** @var string $response */
        $decoded = json_decode($response, true);
        $decodedBody = is_array($decoded) ? $decoded : ['raw' => $response];

        if ($statusCode >= 200 && $statusCode < 300) {
            return $decodedBody;
        }

        $message = $decodedBody['message'] ?? $decodedBody['error'] ?? 'An unexpected error occurred.';
        throw new MainlayerException((string) $message, $statusCode, $decodedBody);
    }
}
