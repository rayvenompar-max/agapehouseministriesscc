<?php
/**
 * Model\DirectMessage
 *
 * Represents a single direct message in a conversation
 */
declare(strict_types=1);

namespace Model;

class DirectMessage
{
    public function __construct(
        public readonly int    $id,
        public readonly int    $conversationId,
        public readonly int    $senderId,
        public readonly string $body,
        public readonly bool   $isRead,
        public readonly string $createdAt,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:             (int) $row['id'],
            conversationId: (int) $row['conversation_id'],
            senderId:       (int) $row['sender_id'],
            body:           $row['body'],
            isRead:         (bool) $row['is_read'],
            createdAt:      $row['created_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'conversation_id' => $this->conversationId,
            'sender_id'       => $this->senderId,
            'body'            => $this->body,
            'is_read'         => $this->isRead,
            'created_at'      => $this->createdAt,
        ];
    }
}
