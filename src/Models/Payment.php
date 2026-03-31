<?php

declare(strict_types=1);

namespace Mainlayer\Models;

/**
 * Represents a Mainlayer payment transaction.
 *
 * A payment is created when a buyer pays for access to a resource.
 * The status field reflects the current state of the transaction.
 */
final class Payment
{
    /**
     * @param string      $id          Unique payment identifier.
     * @param string      $resourceId  ID of the resource that was paid for.
     * @param string      $payerWallet Wallet identifier of the buyer.
     * @param float       $amount      Amount charged.
     * @param string      $status      Transaction status: 'pending'|'completed'|'failed'.
     * @param string|null $createdAt   ISO-8601 creation timestamp.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $resourceId,
        public readonly string $payerWallet,
        public readonly float $amount,
        public readonly string $status,
        public readonly ?string $createdAt = null,
    ) {}

    /**
     * Constructs a Payment from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id:          (string) ($data['id'] ?? ''),
            resourceId:  (string) ($data['resource_id'] ?? ''),
            payerWallet: (string) ($data['payer_wallet'] ?? ''),
            amount:      (float)  ($data['amount'] ?? 0.0),
            status:      (string) ($data['status'] ?? 'pending'),
            createdAt:   isset($data['created_at']) ? (string) $data['created_at'] : null,
        );
    }

    /**
     * Returns true when the payment has been successfully processed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Returns true when the payment is still pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
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
            'resource_id'  => $this->resourceId,
            'payer_wallet' => $this->payerWallet,
            'amount'       => $this->amount,
            'status'       => $this->status,
            'created_at'   => $this->createdAt,
        ], fn ($v) => $v !== null);
    }
}
