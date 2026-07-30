<?php
/**
 * Model\Comment
 *
 * Represents a single feed comment (or reply when parent_id is set).
 */
declare(strict_types=1);

namespace Model;

class Comment
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $memberId,
        public readonly string  $memberDisplayName,
        public readonly string  $memberUsername,
        public readonly ?string $memberProfilePicture,
        public readonly string  $targetType,
        public readonly int     $targetId,
        public readonly ?int    $parentId,
        public readonly string  $body,
        public readonly int     $likeCount,
        public readonly string  $createdAt,
    ) {}

    public function toArray(): array
    {
        return [
            'id'                     => $this->id,
            'member_id'              => $this->memberId,
            'member_display_name'    => $this->memberDisplayName,
            'member_username'        => $this->memberUsername,
            'member_profile_picture' => $this->memberProfilePicture,
            'target_type'            => $this->targetType,
            'target_id'              => $this->targetId,
            'parent_id'              => $this->parentId,
            'body'                   => $this->body,
            'like_count'             => $this->likeCount,
            'created_at'             => $this->createdAt,
        ];
    }
}
