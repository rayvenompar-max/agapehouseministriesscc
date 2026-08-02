<?php
/**
 * Repository\ContactRepository
 */
declare(strict_types=1);

namespace Repository;

use Model\ContactMessage;
use PDO;

class ContactRepository
{
    public function __construct(private readonly PDO $db) {}

    public function create(
        string $name,
        string $email,
        string $reason,
        string $message,
        ?int   $memberId = null
    ): ContactMessage {
        $stmt = $this->db->prepare(
            'INSERT INTO contact_messages (name, email, member_id, reason, message, status, created_at)
             VALUES (:name, :email, :member_id, :reason, :message, :status, NOW())'
        );
        $stmt->execute([
            'name'      => $name,
            'email'     => $email,
            'member_id' => $memberId,
            'reason'    => $reason,
            'message'   => $message,
            'status'    => 'unread',
        ]);

        return $this->findById((int) $this->db->lastInsertId());
    }

    public function findById(int $id): ?ContactMessage
    {
        $stmt = $this->db->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Return all messages ordered newest-first.
     *
     * @return ContactMessage[]
     */
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM contact_messages ORDER BY created_at DESC'
        );
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /**
     * Flip status to 'read' (idempotent — only updates when currently 'unread' or 'replied').
     * Used when a member sends a follow-up so the admin knows to check again.
     */
    public function markRead(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE contact_messages SET status = 'read'
             WHERE id = :id AND status = 'unread'"
        );
        $stmt->execute(['id' => $id]);
    }

    private function hydrate(array $row): ContactMessage
    {
        return new ContactMessage(
            id:        (int) $row['id'],
            name:            $row['name'],
            email:           $row['email'],
            memberId:        isset($row['member_id']) ? (int) $row['member_id'] : null,
            reason:          $row['reason'],
            message:         $row['message'],
            status:          $row['status'],
            createdAt:       $row['created_at'],
        );
    }

    // ── Chat methods ──────────────────────────────────────────────────────────

    /**
     * Add a chat message to an existing contact thread.
     * sender_type is 'admin' or 'member'; sender_id is 0 for admin.
     */
    public function addChatMessage(
        int    $contactMessageId,
        string $senderType,
        int    $senderId,
        string $body
    ): array {
        $stmt = $this->db->prepare(
            'INSERT INTO contact_chat_messages
             (contact_message_id, sender_type, sender_id, body, created_at)
             VALUES (:cid, :stype, :sid, :body, NOW())'
        );
        $stmt->execute([
            'cid'   => $contactMessageId,
            'stype' => $senderType,
            'sid'   => $senderId,
            'body'  => $body,
        ]);

        // Fetch the inserted row so we return full data including id/created_at
        $id = (int) $this->db->lastInsertId();
        $row = $this->db->prepare(
            'SELECT ccm.*, m.display_name AS sender_name, m.profile_picture AS sender_picture
             FROM contact_chat_messages ccm
             LEFT JOIN members m ON m.id = ccm.sender_id AND ccm.sender_type = "member"
             WHERE ccm.id = :id LIMIT 1'
        );
        $row->execute(['id' => $id]);
        return $row->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Return all chat messages for a contact thread, oldest first.
     *
     * @return array[]
     */
    public function getChatMessages(int $contactMessageId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ccm.*, m.display_name AS sender_name, m.profile_picture AS sender_picture
             FROM contact_chat_messages ccm
             LEFT JOIN members m ON m.id = ccm.sender_id AND ccm.sender_type = "member"
             WHERE ccm.contact_message_id = :cid
             ORDER BY ccm.created_at ASC'
        );
        $stmt->execute(['cid' => $contactMessageId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Flip status to 'replied'.
     */
    public function markReplied(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE contact_messages SET status = 'replied' WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }

    /**
     * Return all contact threads for a specific member, with unread count.
     * Threads are ordered by most recent activity (latest chat message or creation date).
     *
     * @return array[] Each with: id, reason, message, created_at, unread_admin_replies
     */
    public function findThreadsByMember(int $memberId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                cm.id,
                cm.reason,
                cm.message,
                cm.created_at,
                cm.status,
                COALESCE(
                    (SELECT COUNT(*)
                     FROM contact_chat_messages ccm
                     WHERE ccm.contact_message_id = cm.id
                       AND ccm.sender_type = "admin"
                       AND ccm.created_at > COALESCE(
                           (SELECT MAX(created_at)
                            FROM contact_chat_messages
                            WHERE contact_message_id = cm.id AND sender_type = "member"),
                           cm.created_at
                       )
                    ), 0
                ) AS unread_admin_replies,
                COALESCE(
                    (SELECT MAX(created_at) FROM contact_chat_messages WHERE contact_message_id = cm.id),
                    cm.created_at
                ) AS last_activity
             FROM contact_messages cm
             WHERE cm.member_id = :member_id
             ORDER BY last_activity DESC'
        );
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
