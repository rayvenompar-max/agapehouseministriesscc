<?php
declare(strict_types=1);

namespace Repository;

use PDO;

class NotificationRepository
{
    public function __construct(private readonly PDO $db) {}

    /**
     * Insert a notification.
     * Uses INSERT IGNORE so duplicate unique-keyed rows (e.g. same person liking twice) are silently skipped.
     * Never notifies yourself.
     */
    public function create(
        int    $recipientId,
        int    $actorId,
        string $type,
        string $targetType,
        int    $targetId,
        string $targetTitle
    ): void {
        if ($recipientId === $actorId) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO notifications
             (recipient_id, actor_id, type, target_type, target_id, target_title)
             VALUES (:recipient_id, :actor_id, :type, :target_type, :target_id, :target_title)'
        );
        $stmt->execute([
            'recipient_id' => $recipientId,
            'actor_id'     => $actorId,
            'type'         => $type,
            'target_type'  => $targetType,
            'target_id'    => $targetId,
            'target_title' => mb_substr($targetTitle, 0, 255),
        ]);
    }

    /**
     * Insert a broadcast notification (from the system / admin).
     * actor_id is stored as 0 so it never matches a member row.
     * The frontend will display "Agape House Ministries" with the church logo.
     */
    public function createBroadcast(
        int    $recipientId,
        string $type,
        string $targetType,
        int    $targetId,
        string $targetTitle
    ): void {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO notifications
             (recipient_id, actor_id, type, target_type, target_id, target_title)
             VALUES (:recipient_id, 0, :type, :target_type, :target_id, :target_title)'
        );
        $stmt->execute([
            'recipient_id' => $recipientId,
            'type'         => $type,
            'target_type'  => $targetType,
            'target_id'    => $targetId,
            'target_title' => mb_substr($targetTitle, 0, 255),
        ]);
    }

    /**
     * Return the most recent notifications for a member, newest first.
     * Uses LEFT JOIN so broadcast notifications (actor_id = 0) are included
     * even though they have no matching member row.
     */
    public function findForMember(int $recipientId, int $limit = 25): array
    {
        $stmt = $this->db->prepare(
            'SELECT n.id, n.type, n.target_type, n.target_id, n.target_title,
                    n.is_read, n.created_at,
                    m.display_name    AS actor_name,
                    m.username        AS actor_username,
                    m.profile_picture AS actor_picture
             FROM notifications n
             LEFT JOIN members m ON m.id = n.actor_id AND n.actor_id != 0
             WHERE n.recipient_id = :rid
             ORDER BY n.created_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue('rid', $recipientId, PDO::PARAM_INT);
        $stmt->bindValue('lim', $limit,       PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Count unread notifications for a member. */
    public function countUnread(int $recipientId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM notifications WHERE recipient_id = :rid AND is_read = 0'
        );
        $stmt->execute(['rid' => $recipientId]);
        return (int) $stmt->fetchColumn();
    }

    /** Mark all notifications as read for a member. */
    public function markAllRead(int $recipientId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE recipient_id = :rid AND is_read = 0'
        );
        $stmt->execute(['rid' => $recipientId]);
    }

    /** Delete all notifications for a member. */
    public function clearAll(int $recipientId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM notifications WHERE recipient_id = :rid'
        );
        $stmt->execute(['rid' => $recipientId]);
    }

    /** Mark a single notification as read. */
    public function markRead(int $notificationId, int $recipientId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1
             WHERE id = :id AND recipient_id = :rid'
        );
        $stmt->execute(['id' => $notificationId, 'rid' => $recipientId]);
    }

    /** Remove a post-like notification when someone un-likes. */
    public function deleteLike(
        int    $recipientId,
        int    $actorId,
        string $targetType,
        int    $targetId
    ): void {
        $stmt = $this->db->prepare(
            'DELETE FROM notifications
             WHERE recipient_id = :rid AND actor_id = :aid
               AND type = "like" AND target_type = :tt AND target_id = :ti'
        );
        $stmt->execute([
            'rid' => $recipientId,
            'aid' => $actorId,
            'tt'  => $targetType,
            'ti'  => $targetId,
        ]);
    }

    /** Remove a comment-like notification when someone un-likes a comment. */
    public function deleteCommentLike(int $recipientId, int $actorId, int $commentId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM notifications
             WHERE recipient_id = :rid AND actor_id = :aid
               AND type = "comment_like" AND target_id = :cid'
        );
        $stmt->execute(['rid' => $recipientId, 'aid' => $actorId, 'cid' => $commentId]);
    }

    /** Remove a follow/follow_back notification when someone unfollows. */
    public function deleteFollow(int $recipientId, int $actorId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM notifications
             WHERE recipient_id = :rid AND actor_id = :aid
               AND type IN ("follow", "follow_back")'
        );
        $stmt->execute(['rid' => $recipientId, 'aid' => $actorId]);
    }
}
