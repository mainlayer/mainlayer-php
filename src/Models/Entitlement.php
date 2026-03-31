<?php

declare(strict_types=1);

namespace Mainlayer\Models;

/**
 * Represents a payer's access entitlement to a Mainlayer resource.
 *
 * An entitlement is granted when a successful payment has been made.
 * The `hasAccess` property is the authoritative gate check.
 */
final class Entitlement
{
    /**
     * @param bool        $hasAccess   Whether the payer currently has access.
     * @param string      $resourceId  ID of the resource being accessed.
     * @param string      $payerWallet Wallet identifier of the buyer.
     * @param string|null $expiresAt   ISO-8601 expiry timestamp, or null for lifetime access.
     */
    public function __construct(
        public readonly bool $hasAccess,
        public readonly string $resourceId,
        public readonly string $payerWallet,
        public readonly ?string $expiresAt = null,
    ) {}

    /**
     * Constructs an Entitlement from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            hasAccess:   (bool)   ($data['has_access'] ?? false),
            resourceId:  (string) ($data['resource_id'] ?? ''),
            payerWallet: (string) ($data['payer_wallet'] ?? ''),
            expiresAt:   isset($data['expires_at']) ? (string) $data['expires_at'] : null,
        );
    }

    /**
     * Returns true when the entitlement is active and not expired.
     */
    public function isActive(): bool
    {
        if (!$this->hasAccess) {
            return false;
        }

        if ($this->expiresAt === null) {
            return true;
        }

        return strtotime($this->expiresAt) > time();
    }

    /**
     * Serialises the model back to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'has_access'   => $this->hasAccess,
            'resource_id'  => $this->resourceId,
            'payer_wallet' => $this->payerWallet,
            'expires_at'   => $this->expiresAt,
        ], fn ($v) => $v !== null && $v !== false);
    }
}
