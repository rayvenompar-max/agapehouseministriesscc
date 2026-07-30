-- =============================================================
-- posts_shares table
-- Tracks when members share articles/media to their wall
-- =============================================================

USE daybreak;

-- Table for tracking shared content
CREATE TABLE IF NOT EXISTS posts_shares (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id    INT UNSIGNED NOT NULL COMMENT 'Member who shared',
    content_type ENUM('article','media','prayer') NOT NULL COMMENT 'Type of content shared',
    content_id   INT UNSIGNED NOT NULL COMMENT 'ID of the shared content',
    caption      TEXT DEFAULT NULL COMMENT 'Optional caption when sharing',
    shared_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_member_id (member_id),
    INDEX idx_content (content_type, content_id),
    INDEX idx_shared_at (shared_at)
) ENGINE=InnoDB;

-- Add member_id foreign key constraint if members table exists
-- ALTER TABLE posts_shares ADD CONSTRAINT fk_share_member 
-- FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE;
