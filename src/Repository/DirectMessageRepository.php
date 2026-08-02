<?php
/**
 * Repository\DirectMessageRepository
 *
 * Data access for direct messages between members
 */
declare(strict_types=1);

namespace Repository;

use Model\DirectMessage;
use Model\DirectMessageConversation;

class DirectMessageRepository
{
    public function __construct(private readonly \PDO $pdo) {}

    /**
     * Find or create a conversation between two members
     */
    public function findOrCreateConversation(int $memberOneId, int $memberTwoId): DirectMessageConversation
    {
        // Ensure consistent ordering to avoid duplicate conversations
        $lower  = min($memberOneId, $memberTwoId);
        $higher = max($memberOneId, $memberTwoId);

        // Try to find existing conversation
        $stmt = $this->pdo->prepare(
            'SELECT * FROM direct_message_conversations 
             WHERE member_one_id = ? AND member_two_id = ?'
        );
        $stmt->execute([$lower, $higher]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            return DirectMessageConversation::fromArray($row);
        }

        // Create new conversation
        $stmt = $this->pdo->prepare(
            'INSERT INTO direct_message_conversations 
             (member_one_id, member_two_id, created_at) 
             VALUES (?, ?, NOW())'
        );
        $stmt->execute([$lower, $higher]);

        $id = (int) $this->pdo->lastInsertId();

        return new DirectMessageConversation(
            id:            $id,
            memberOneId:   $lower,
            memberTwoId:   $higher,
            lastMessageAt: null,
            createdAt:     date('Y-m-d H:i:s'),
        );
    }

    /**
     * Get a conversation by ID
     */
    public function findConversationById(int $conversationId): ?DirectMessageConversation
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM direct_message_conversations WHERE id = ?'
        );
        $stmt->execute([$conversationId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ? DirectMessageConversation::fromArray($row) : null;
    }

    /**
     * Get all conversations for a member with metadata
     */
    public function findConversationsByMember(int $memberId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT 
                c.*,
                CASE 
                    WHEN c.member_one_id = ? THEN c.member_two_id
                    ELSE c.member_one_id
                END as other_member_id,
                m.display_name as other_member_name,
                m.username as other_member_username,
                m.profile_picture as other_member_picture,
                (SELECT COUNT(*) FROM direct_messages 
                 WHERE conversation_id = c.id 
                 AND sender_id != ? 
                 AND is_read = 0) as unread_count,
                (SELECT body FROM direct_messages 
                 WHERE conversation_id = c.id 
                 ORDER BY created_at DESC LIMIT 1) as last_message_body
             FROM direct_message_conversations c
             JOIN members m ON (
                CASE 
                    WHEN c.member_one_id = ? THEN c.member_two_id
                    ELSE c.member_one_id
                END = m.id
             )
             WHERE c.member_one_id = ? OR c.member_two_id = ?
             ORDER BY COALESCE(c.last_message_at, c.created_at) DESC'
        );
        $stmt->execute([$memberId, $memberId, $memberId, $memberId, $memberId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all messages in a conversation
     */
    public function findMessagesByConversation(int $conversationId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT dm.*, m.display_name as sender_name, m.username as sender_username, 
                    m.profile_picture as sender_picture
             FROM direct_messages dm
             JOIN members m ON dm.sender_id = m.id
             WHERE dm.conversation_id = ?
             ORDER BY dm.created_at ASC'
        );
        $stmt->execute([$conversationId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Add a message to a conversation
     */
    public function addMessage(int $conversationId, int $senderId, string $body): DirectMessage
    {
        if (empty(trim($body))) {
            throw new \InvalidArgumentException('Message body cannot be empty.');
        }

        if (mb_strlen($body) > 3000) {
            throw new \InvalidArgumentException('Message too long (max 3000 characters).');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO direct_messages 
             (conversation_id, sender_id, body, created_at) 
             VALUES (?, ?, ?, NOW())'
        );
        $stmt->execute([$conversationId, $senderId, trim($body)]);

        $messageId = (int) $this->pdo->lastInsertId();

        // Update conversation's last_message_at
        $updateStmt = $this->pdo->prepare(
            'UPDATE direct_message_conversations 
             SET last_message_at = NOW() 
             WHERE id = ?'
        );
        $updateStmt->execute([$conversationId]);

        return new DirectMessage(
            id:             $messageId,
            conversationId: $conversationId,
            senderId:       $senderId,
            body:           trim($body),
            isRead:         false,
            createdAt:      date('Y-m-d H:i:s'),
        );
    }

    /**
     * Mark all messages in a conversation as read by the recipient
     */
    public function markConversationAsRead(int $conversationId, int $readerId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE direct_messages 
             SET is_read = 1 
             WHERE conversation_id = ? 
             AND sender_id != ? 
             AND is_read = 0'
        );
        $stmt->execute([$conversationId, $readerId]);
    }

    /**
     * Get total unread message count for a member across all conversations
     */
    public function getUnreadCountForMember(int $memberId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) as count
             FROM direct_messages dm
             JOIN direct_message_conversations c ON dm.conversation_id = c.id
             WHERE (c.member_one_id = ? OR c.member_two_id = ?)
             AND dm.sender_id != ?
             AND dm.is_read = 0'
        );
        $stmt->execute([$memberId, $memberId, $memberId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) ($row['count'] ?? 0);
    }
}
