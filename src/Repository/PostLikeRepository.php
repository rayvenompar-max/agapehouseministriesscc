<?php
declare(strict_types=1);

namespace Repository;

use PDO;

class PostLikeRepository
{
    public function __construct(private readonly PDO $db) {}

    /**
     * Toggle a like. Returns true if now liked, false if unliked.
     */
    public function toggle(int $memberId, string $targetType, int $targetId): bool
    {
        if ($this->hasLiked($memberId, $targetType, $targetId)) {
            $stmt = $this->db->prepare(
                'DELETE FROM post_likes
                  WHERE member_id = :mid AND target_type = :tt AND target_id = :tid'
            );
            $stmt->execute(['mid' => $memberId, 'tt' => $targetType, 'tid' => $targetId]);
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO post_likes (member_id, target_type, target_id)
             VALUES (:mid, :tt, :tid)'
        );
        $stmt->execute(['mid' => $memberId, 'tt' => $targetType, 'tid' => $targetId]);
        return true;
    }

    /** Whether the given member has already liked this target. */
    public function hasLiked(int $memberId, string $targetType, int $targetId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM post_likes
              WHERE member_id = :mid AND target_type = :tt AND target_id = :tid
              LIMIT 1'
        );
        $stmt->execute(['mid' => $memberId, 'tt' => $targetType, 'tid' => $targetId]);
        return (bool) $stmt->fetchColumn();
    }

    /** Total like count for a target. */
    public function countFor(string $targetType, int $targetId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM post_likes
              WHERE target_type = :tt AND target_id = :tid'
        );
        $stmt->execute(['tt' => $targetType, 'tid' => $targetId]);
        return (int) $stmt->fetchColumn();
    }
}
