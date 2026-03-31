<?php

declare(strict_types=1);

namespace Mainlayer;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mainlayer\Exception\AuthenticationException;
use Mainlayer\Exception\MainlayerException;
use Mainlayer\Exception\NotFoundException;
use Mainlayer\Exception\RateLimitException;
use Throwable;

/**
 * Low-level HTTP client that wraps Guzzle, handles authentication,
 * and implements retry logic with exponential backoff.
 *
 * @internal This class is not part of the public API surface.
 */
class HttpClient
{
    private const BASE_URL = 'https://api.mainlayer.xyz';
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_MS = 500;
    private const SDK_VERSION = '1.0.0';

    private readonly Client $guzzle;

    /**
     * @param array<string, mixed> $options Additional Guzzle client options.
     */
    public function __construct(
        private readonly string $apiKey,
        array $options = [],
        ?Client $guzzle = null,
    ) {
        $this->guzzle = $guzzle ?? new Client(array_merge([
            'base_uri' => self::BASE_URL,
            'handler' => $this->buildHandlerStack(),
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'mainlayer-php/' . self::SDK_VERSION . ' PHP/' . PHP_VERSION,
            ],
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => false,
        ], $options));
    }

    /**
     * Performs a GET request and returns the decoded response body.
     *
     * @param  array<string, mixed> $query Query string parameters.
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, ['query' => $query]);
    }

    /**
     * Performs a POST request and returns the decoded response body.
     *
     * @param  array<string, mixed> $body JSON body parameters.
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function post(string $path, array $body = []): array
    {
        return $this->request('POST', $path, ['json' => $body]);
    }

    /**
     * Performs a PATCH request and returns the decoded response body.
     *
     * @param  array<string, mixed> $body JSON body parameters.
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function patch(string $path, array $body = []): array
    {
        return $this->request('PATCH', $path, ['json' => $body]);
    }

    /**
     * Performs a DELETE request and returns the decoded response body.
     *
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    /**
     * @param  array<string, mixed> $options
     * @return array<string, mixed>
     *
     * @throws MainlayerException
     */
    private function request(string $method, string $path, array $options = []): array
    {
        $attempt = 0;

        while (true) {
            try {
                $response = $this->guzzle->request($method, ltrim($path, '/'), $options);
                $statusCode = $response->getStatusCode();
                $body = $this->decodeBody($response);

                if ($statusCode >= 200 && $statusCode < 300) {
                    return $body;
                }

                if ($statusCode === 429 || $statusCode >= 500) {
                    if ($attempt < self::MAX_RETRIES) {
                        $attempt++;
                        $this->sleep($attempt);
                        continue;
                    }
                }

                $this->throwForStatus($statusCode, $body);
            } catch (ConnectException $e) {
                if ($attempt < self::MAX_RETRIES) {
                    $attempt++;
                    $this->sleep($attempt);
                    continue;
                }
                throw new MainlayerException(
                    "Connection error: {$e->getMessage()}",
                    0,
                    [],
                    $e,
                );
            } catch (MainlayerException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw new MainlayerException(
                    "Unexpected error: {$e->getMessage()}",
                    0,
                    [],
                    $e,
                );
            }
        }
    }

    /**
     * Decodes the JSON body of a Guzzle response.
     *
     * @return array<string, mixed>
     */
    private function decodeBody(Response $response): array
    {
        $contents = (string) $response->getBody();

        if ($contents === '') {
            return [];
        }

        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            return ['raw' => $contents];
        }

        return $decoded;
    }

    /**
     * Maps an HTTP status code to the appropriate exception.
     *
     * @param array<string, mixed> $body
     *
     * @throws MainlayerException
     */
    private function throwForStatus(int $statusCode, array $body): never
    {
        $message = $body['message'] ?? $body['error'] ?? 'An unexpected error occurred.';

        throw match (true) {
            $statusCode === 401 => new AuthenticationException((string) $message, $statusCode, $body),
            $statusCode === 404 => new NotFoundException((string) $message, $statusCode, $body),
            $statusCode === 429 => new RateLimitException((string) $message, $statusCode, $body),
            default => new MainlayerException((string) $message, $statusCode, $body),
        };
    }

    /**
     * Sleeps for an exponentially increasing delay.
     *
     * Attempt 1 → 500 ms, attempt 2 → 1 000 ms, attempt 3 → 2 000 ms.
     */
    private function sleep(int $attempt): void
    {
        $delayMs = self::RETRY_DELAY_MS * (2 ** ($attempt - 1));
        usleep($delayMs * 1000);
    }

    private function buildHandlerStack(): HandlerStack
    {
        return HandlerStack::create();
    }
}
