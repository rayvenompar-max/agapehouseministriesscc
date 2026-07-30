<?php
/**
 * Model\ContactMessage
 *
 * Represents an inbound contact / connect form submission.
 */
declare(strict_types=1);

namespace Model;

class ContactMessage
{
    public function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $reason,    // 'Just saying hi' | 'Prayer request' | etc.
        public readonly string $message,
        public readonly string $status,    // unread | read | replied
        public readonly string $createdAt,
    ) {}

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'reason'     => $this->reason,
            'message'    => $this->message,
            'status'     => $this->status,
            'created_at' => $this->createdAt,
        ];
    }
}
