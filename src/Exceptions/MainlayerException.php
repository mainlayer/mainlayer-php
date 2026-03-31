<?php

declare(strict_types=1);

namespace Mainlayer\Exceptions;

use Throwable;

/**
 * Base exception for all Mainlayer SDK errors.
 *
 * This class lives in the Mainlayer\Exceptions namespace and is the canonical
 * exception type thrown by the façade client ({@see \Mainlayer\Mainlayer}).
 * The full resource-oriented client uses {@see \Mainlayer\Exception\MainlayerException}.
 */
class MainlayerException extends \RuntimeException
{
    /**
     * @param string               $message    Human-readable error message.
     * @param int                  $statusCode HTTP status code returned by the API, or 0.
     * @param array<string, mixed> $body       Decoded response body, if available.
     * @param Throwable|null       $previous   The previous exception, if any.
     */
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
        private readonly array $body = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Returns the HTTP status code returned by the Mainlayer API.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Returns the decoded JSON response body from the API, if available.
     *
     * @return array<string, mixed>
     */
    public function getBody(): array
    {
        return $this->body;
    }
}
