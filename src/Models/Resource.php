<?php

declare(strict_types=1);

namespace Mainlayer\Models;

/**
 * Represents a Mainlayer billable resource.
 *
 * Resources are the core primitive of Mainlayer — they represent any
 * monetisable unit (API endpoint, file, page, etc.) that you want to
 * gate behind a payment.
 */
final class Resource
{
    /**
     * @param string      $id          Unique resource identifier.
     * @param string      $slug        URL-friendly identifier chosen by the vendor.
     * @param string      $type        Resource type: 'api'|'file'|'endpoint'|'page'.
     * @param float       $price       Price per unit.
     * @param string      $feeModel    Billing model: 'one_time'|'subscription'|'pay_per_call'.
     * @param string|null $description Optional human-readable description.
     * @param string|null $callbackUrl Optional callback URL called on successful payment.
     * @param string|null $createdAt   ISO-8601 creation timestamp.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $slug,
        public readonly string $type,
        public readonly float $price,
        public readonly string $feeModel,
        public readonly ?string $description = null,
        public readonly ?string $callbackUrl = null,
        public readonly ?string $createdAt = null,
    ) {}

    /**
     * Constructs a Resource from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id:          (string) ($data['id'] ?? ''),
            slug:        (string) ($data['slug'] ?? ''),
            type:        (string) ($data['type'] ?? ''),
            price:       (float)  ($data['price'] ?? $data['price_usdc'] ?? 0.0),
            feeModel:    (string) ($data['fee_model'] ?? ''),
            description: isset($data['description']) ? (string) $data['description'] : null,
            callbackUrl: isset($data['callback_url']) ? (string) $data['callback_url'] : null,
            createdAt:   isset($data['created_at']) ? (string) $data['created_at'] : null,
        );
    }

    /**
     * Serialises the model back to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id'           => $this->id,
            'slug'         => $this->slug,
            'type'         => $this->type,
            'price'        => $this->price,
            'fee_model'    => $this->feeModel,
            'description'  => $this->description,
            'callback_url' => $this->callbackUrl,
            'created_at'   => $this->createdAt,
        ], fn ($v) => $v !== null);
    }
}
