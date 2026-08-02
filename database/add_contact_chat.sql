-- =============================================================
-- Migration: Contact Chat System
-- Adds member_id to contact_messages, creates contact_chat_messages,
-- and expands notification ENUMs.
-- Run once: mysql -u root daybreak < database/add_contact_chat.sql
-- =============================================================

USE daybreak;

-- 1. Link contact messages to a member (optional — guests can still submit)
ALTER TABLE contact_messages
    ADD COLUMN member_id INT UNSIGNED NULL DEFAULT NULL
        COMMENT 'Set when a logged-in member submits the form'
        AFTER email,
    ADD INDEX idx_member (member_id);

-- 2. Chat messages table (thread per contact_message)
CREATE TABLE IF NOT EXISTS contact_chat_messages (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_message_id INT UNSIGNED NOT NULL COMMENT 'Parent contact_messages.id',
    sender_type        ENUM('member','admin') NOT NULL,
    sender_id          INT UNSIGNED NOT NULL DEFAULT 0
        COMMENT 'member.id for member, 0 for admin',
    body               TEXT NOT NULL,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_thread (contact_message_id, created_at)
) ENGINE=InnoDB;

-- 3. Expand notification type ENUM (add contact_reply)
ALTER TABLE notifications
    MODIFY type ENUM(
        'like',
        'comment',
        'share',
        'comment_like',
        'comment_reply',
        'follow',
        'follow_back',
        'new_event',
        'new_announcement',
        'contact_reply'
    ) NOT NULL;

-- 4. Expand notification target_type ENUM (add contact_message)
ALTER TABLE notifications
    MODIFY target_type ENUM(
        'article',
        'media',
        'announcement',
        'event',
        'contact_message'
    ) NOT NULL;
