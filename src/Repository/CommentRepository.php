<?php
/**
 * Repository\CommentRepository
 */
declare(strict_types=1);

namespace Repository;

use Model\Comment;
use PDO;

class CommentRepository
{
    public function __construct(private readonly PDO $db) {}

    /**
     * Return top-level comments for a target (parent_id IS NULL), oldest first.
     * Replies are fetched separately via findReplies().
     */
    public function findByTarget(string $targetType, int $targetId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, m.display_name AS member_display_name, m.username AS member_username,
                    m.profile_picture AS member_profile_picture
             FROM comments c
             INNER JOIN members m ON m.id = c.member_id
             WHERE c.target_type = :type AND c.target_id = :id AND c.parent_id IS NULL
             ORDER BY c.created_at ASC'
        );
        $stmt->execute(['type' => $targetType, 'id' => $targetId]);
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /** Return all replies (all depths) under a top-level comment, oldest first. */
    public function findReplies(int $parentId): array
    {
        // Recursive CTE collects all descendants of the top-level comment so that
        // replies-to-replies (stored with a child's id as parent_id) are included.
        $stmt = $this->db->prepare(
            'WITH RECURSIVE descendants AS (
                SELECT c.*, m.display_name AS member_display_name, m.username AS member_username,
                       m.profile_picture AS member_profile_picture
                FROM comments c
                INNER JOIN members m ON m.id = c.member_id
                WHERE c.parent_id = :parent_id
                UNION ALL
                SELECT c2.*, m2.display_name, m2.username, m2.profile_picture
                FROM comments c2
                INNER JOIN members m2 ON m2.id = c2.member_id
                INNER JOIN descendants d ON c2.parent_id = d.id
             )
             SELECT * FROM descendants ORDER BY created_at ASC'
        );
        $stmt->execute(['parent_id' => $parentId]);
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function countByTarget(string $targetType, int $targetId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM comments WHERE target_type = :type AND target_id = :id'
        );
        $stmt->execute(['type' => $targetType, 'id' => $targetId]);
        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): ?Comment
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, m.display_name AS member_display_name, m.username AS member_username,
                    m.profile_picture AS member_profile_picture
             FROM comments c
             INNER JOIN members m ON m.id = c.member_id
             WHERE c.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /** Create a top-level comment or a reply (parent_id optional). */
    public function create(
        int    $memberId,
        string $targetType,
        int    $targetId,
        string $body,
        ?int   $parentId = null
    ): Comment {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (member_id, target_type, target_id, parent_id, body, like_count, created_at)
             VALUES (:member_id, :target_type, :target_id, :parent_id, :body, 0, NOW())'
        );
        $stmt->execute([
            'member_id'   => $memberId,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'parent_id'   => $parentId,
            'body'        => $body,
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    /** Increment like_count and return the new count. */
    public function incrementLike(int $commentId): int
    {
        $this->db->prepare('UPDATE comments SET like_count = like_count + 1 WHERE id = :id')
                 ->execute(['id' => $commentId]);
        $stmt = $this->db->prepare('SELECT like_count FROM comments WHERE id = :id');
        $stmt->execute(['id' => $commentId]);
        return (int) $stmt->fetchColumn();
    }

    /** Decrement like_count (floor 0) and return the new count. */
    public function decrementLike(int $commentId): int
    {
        $this->db->prepare('UPDATE comments SET like_count = GREATEST(0, like_count - 1) WHERE id = :id')
                 ->execute(['id' => $commentId]);
        $stmt = $this->db->prepare('SELECT like_count FROM comments WHERE id = :id');
        $stmt->execute(['id' => $commentId]);
        return (int) $stmt->fetchColumn();
    }

    public function delete(int $id, int $memberId): bool
    {
        // Also deletes replies (CASCADE is not set, so delete children first)
        $this->db->prepare('DELETE FROM comments WHERE parent_id = :id')->execute(['id' => $id]);
        $stmt = $this->db->prepare(
            'DELETE FROM comments WHERE id = :id AND member_id = :member_id'
        );
        $stmt->execute(['id' => $id, 'member_id' => $memberId]);
        return $stmt->rowCount() > 0;
    }

    private function hydrate(array $row): Comment
    {
        return new Comment(
            id:                   (int)  $row['id'],
            memberId:             (int)  $row['member_id'],
            memberDisplayName:           $row['member_display_name'],
            memberUsername:              $row['member_username'],
            memberProfilePicture:        $row['member_profile_picture'] ?? null,
            targetType:                  $row['target_type'],
            targetId:             (int)  $row['target_id'],
            parentId:             isset($row['parent_id']) && $row['parent_id'] !== null
                                    ? (int) $row['parent_id'] : null,
            body:                        $row['body'],
            likeCount:            (int)  ($row['like_count'] ?? 0),
            createdAt:                   $row['created_at'],
        );
    }
}
