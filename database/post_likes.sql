-- =============================================================
-- post_likes table
-- Tracks per-member likes on articles, media, and announcements
-- Run once in phpMyAdmin or: mysql -u root daybreak < post_likes.sql
-- =============================================================

USE daybreak;

CREATE TABLE IF NOT EXISTS post_likes (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id    INT UNSIGNED NOT NULL COMMENT 'Member who liked',
    target_type  ENUM('article','media','announcement') NOT NULL,
    target_id    INT UNSIGNED NOT NULL COMMENT 'ID of the liked content',
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_like (member_id, target_type, target_id),
    INDEX idx_target  (target_type, target_id),
    INDEX idx_member  (member_id),
    CONSTRAINT fk_post_likes_member
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;
