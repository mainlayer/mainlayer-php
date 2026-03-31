<?php

declare(strict_types=1);

namespace Mainlayer\Exception;

use Throwable;

/**
 * Thrown when the API key is missing, invalid, or expired (HTTP 401).
 */
class AuthenticationException extends MainlayerException
{
    /**
     * @param array<string, mixed> $body
     */
    public function __construct(
        string $message = 'Invalid or missing API key.',
        int $statusCode = 401,
        array $body = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $body, $previous);
    }
}
