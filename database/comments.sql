-- =============================================================
-- Feed Comments table — run once in phpMyAdmin or via CLI
-- =============================================================

USE daybreak;

CREATE TABLE IF NOT EXISTS comments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id   INT UNSIGNED NOT NULL,
    target_type ENUM('media','article','announcement') NOT NULL,
    target_id   INT UNSIGNED NOT NULL,
    body        TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_target (target_type, target_id),
    INDEX idx_member (member_id),
    INDEX idx_created (created_at),
    CONSTRAINT fk_comments_member
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;
