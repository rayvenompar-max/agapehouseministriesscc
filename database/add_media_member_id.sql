-- Add member_id to media table for ownership tracking
-- Run once: mysql -u root daybreak < add_media_member_id.sql

ALTER TABLE media
    ADD COLUMN member_id INT UNSIGNED NULL DEFAULT NULL
        COMMENT 'FK to members.id — NULL means posted by admin or seeded data'
        AFTER posted_by,
    ADD INDEX idx_member_id (member_id);
