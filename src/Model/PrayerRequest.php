<?php
/**
 * Model\PrayerRequest
 *
 * Represents a single entry on the Prayer Wall.
 */
declare(strict_types=1);

namespace Model;

class PrayerRequest
{
    public function __construct(
        public readonly int    $id,
        public readonly string $name,           // '' means anonymous
        public readonly string $category,       // Healing | Family | Guidance | Provision | Thanksgiving
        public readonly string $body,
        public readonly int    $prayCount,
        public readonly string $status,         // pending | approved | rejected
        public readonly string $createdAt,
    ) {}

    public function isAnonymous(): bool
    {
        return $this->name === '' || $this->name === 'Anonymous';
    }

    public function displayName(): string
    {
        return $this->isAnonymous() ? 'Anonymous' : htmlspecialchars($this->name);
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->displayName(),
            'category'   => $this->category,
            'body'       => $this->body,
            'pray_count' => $this->prayCount,
            'status'     => $this->status,
            'created_at' => $this->createdAt,
        ];
    }
}
