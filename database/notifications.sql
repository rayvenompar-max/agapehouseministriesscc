-- =============================================================
-- notifications table
-- Stores per-member notifications for likes, comments, shares
-- =============================================================

USE daybreak;

CREATE TABLE IF NOT EXISTS notifications (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT UNSIGNED NOT NULL COMMENT 'Member who receives the notification',
    actor_id     INT UNSIGNED NOT NULL COMMENT 'Member who triggered the action',
    type         ENUM('like','comment','share','comment_like','comment_reply') NOT NULL,
    target_type  ENUM('article','media','announcement') NOT NULL,
    target_id    INT UNSIGNED NOT NULL COMMENT 'ID of the article/media/announcement',
    target_title VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Cached title for display',
    is_read      TINYINT(1) NOT NULL DEFAULT 0,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipient  (recipient_id, is_read),
    INDEX idx_created    (created_at),
    -- Prevent duplicate like notifications for the same actor+target
    UNIQUE KEY uq_like (recipient_id, actor_id, type, target_type, target_id)
) ENGINE=InnoDB;
