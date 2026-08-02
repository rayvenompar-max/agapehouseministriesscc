<?php
/**
 * Model\DirectMessageConversation
 *
 * Represents a direct message conversation between two members
 */
declare(strict_types=1);

namespace Model;

class DirectMessageConversation
{
    public function __construct(
        public readonly int       $id,
        public readonly int       $memberOneId,
        public readonly int       $memberTwoId,
        public readonly ?string   $lastMessageAt,
        public readonly string    $createdAt,
    ) {}

    /**
     * Get the other member's ID in this conversation
     */
    public function getOtherMemberId(int $currentMemberId): int
    {
        return $this->memberOneId === $currentMemberId 
            ? $this->memberTwoId 
            : $this->memberOneId;
    }

    /**
     * Check if a member is part of this conversation
     */
    public function hasMember(int $memberId): bool
    {
        return $this->memberOneId === $memberId || $this->memberTwoId === $memberId;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            id:            (int) $row['id'],
            memberOneId:   (int) $row['member_one_id'],
            memberTwoId:   (int) $row['member_two_id'],
            lastMessageAt: $row['last_message_at'] ?? null,
            createdAt:     $row['created_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'member_one_id'   => $this->memberOneId,
            'member_two_id'   => $this->memberTwoId,
            'last_message_at' => $this->lastMessageAt,
            'created_at'      => $this->createdAt,
        ];
    }
}
