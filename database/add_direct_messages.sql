-- ============================================================
-- Direct Messages (Member-to-Member) Migration
-- ============================================================

USE daybreak;

-- Direct message conversations between members
CREATE TABLE IF NOT EXISTS direct_message_conversations (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_one_id     INT UNSIGNED NOT NULL,
    member_two_id     INT UNSIGNED NOT NULL,
    last_message_at   DATETIME NULL,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_member_one (member_one_id),
    INDEX idx_member_two (member_two_id),
    INDEX idx_last_message (last_message_at),
    UNIQUE KEY unique_conversation (member_one_id, member_two_id),
    
    FOREIGN KEY (member_one_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (member_two_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Individual direct messages within a conversation
CREATE TABLE IF NOT EXISTS direct_messages (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id         INT UNSIGNED NOT NULL,
    sender_id               INT UNSIGNED NOT NULL,
    body                    TEXT NOT NULL,
    is_read                 TINYINT(1) NOT NULL DEFAULT 0,
    created_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_conversation (conversation_id),
    INDEX idx_sender (sender_id),
    INDEX idx_created (created_at),
    INDEX idx_is_read (is_read),
    
    FOREIGN KEY (conversation_id) REFERENCES direct_message_conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;
