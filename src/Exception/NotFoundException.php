<?php

declare(strict_types=1);

namespace Mainlayer\Exception;

use Throwable;

/**
 * Thrown when the requested resource does not exist (HTTP 404).
 */
class NotFoundException extends MainlayerException
{
    /**
     * @param array<string, mixed> $body
     */
    public function __construct(
        string $message = 'The requested resource was not found.',
        int $statusCode = 404,
        array $body = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $body, $previous);
    }
}
