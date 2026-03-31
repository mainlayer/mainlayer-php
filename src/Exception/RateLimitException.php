<?php

declare(strict_types=1);

namespace Mainlayer\Exception;

use Throwable;

/**
 * Thrown when the API rate limit is exceeded (HTTP 429).
 *
 * The SDK will automatically retry up to three times with exponential
 * backoff before raising this exception.
 */
class RateLimitException extends MainlayerException
{
    /**
     * @param array<string, mixed> $body
     */
    public function __construct(
        string $message = 'API rate limit exceeded. Please retry after a moment.',
        int $statusCode = 429,
        array $body = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $body, $previous);
    }
}
