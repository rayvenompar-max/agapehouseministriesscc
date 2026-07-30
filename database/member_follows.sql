-- =============================================================
-- Member follows table
-- follower_id  = the member who is following
-- following_id = the member being followed
-- =============================================================

USE daybreak;

CREATE TABLE IF NOT EXISTS member_follows (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    follower_id  INT UNSIGNED NOT NULL,
    following_id INT UNSIGNED NOT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_follow (follower_id, following_id),
    INDEX idx_follower  (follower_id),
    INDEX idx_following (following_id),
    CONSTRAINT fk_follow_follower  FOREIGN KEY (follower_id)  REFERENCES members(id) ON DELETE CASCADE,
    CONSTRAINT fk_follow_following FOREIGN KEY (following_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;    
