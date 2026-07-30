<?php
/**
 * Model\Donation
 *
 * Represents a financial gift record.
 */
declare(strict_types=1);

namespace Model;

class Donation
{
    public function __construct(
        public readonly int    $id,
        public readonly string $donorName,
        public readonly string $donorEmail,
        public readonly float  $amount,
        public readonly string $currency,      // 'USD' default
        public readonly string $frequency,     // 'one_time' | 'monthly'
        public readonly string $tier,          // seed | grow | harvest | custom
        public readonly string $status,        // pending | completed | failed
        public readonly string $createdAt,
    ) {}

    public function formattedAmount(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'donor_name'  => $this->donorName,
            'donor_email' => $this->donorEmail,
            'amount'      => $this->amount,
            'formatted'   => $this->formattedAmount(),
            'currency'    => $this->currency,
            'frequency'   => $this->frequency,
            'tier'        => $this->tier,
            'status'      => $this->status,
            'created_at'  => $this->createdAt,
        ];
    }
}
