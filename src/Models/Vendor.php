<?php

declare(strict_types=1);

namespace Mainlayer\Models;

/**
 * Represents a Mainlayer vendor (seller) account.
 *
 * Vendors create and manage resources, receive payments, and can monitor
 * their revenue through the analytics API.
 */
final class Vendor
{
    /**
     * @param string      $id          Unique vendor identifier.
     * @param string      $name        Display name of the vendor.
     * @param string      $email       Contact email address.
     * @param float       $totalRevenue Total revenue earned across all resources.
     * @param int         $totalPayments Total number of completed payments.
     * @param string|null $createdAt   ISO-8601 account creation timestamp.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly float $totalRevenue = 0.0,
        public readonly int $totalPayments = 0,
        public readonly ?string $createdAt = null,
    ) {}

    /**
     * Constructs a Vendor from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id:            (string) ($data['id'] ?? ''),
            name:          (string) ($data['name'] ?? ''),
            email:         (string) ($data['email'] ?? ''),
            totalRevenue:  (float)  ($data['total_revenue'] ?? 0.0),
            totalPayments: (int)    ($data['total_payments'] ?? 0),
            createdAt:     isset($data['created_at']) ? (string) $data['created_at'] : null,
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
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'total_revenue'  => $this->totalRevenue,
            'total_payments' => $this->totalPayments,
            'created_at'     => $this->createdAt,
        ], fn ($v) => $v !== null);
    }
}
