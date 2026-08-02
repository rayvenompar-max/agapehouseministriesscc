-- =============================================================
-- Migration: Contact Chat System (SAFE / IDEMPOTENT VERSION)
-- Can be run multiple times without errors.
-- =============================================================

USE daybreak;

-- 1. Add member_id to contact_messages (only if it doesn't exist)
SET @dbname = DATABASE();
SET @tablename = 'contact_messages';
SET @columnname = 'member_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT UNSIGNED NULL DEFAULT NULL COMMENT 'Set when a logged-in member submits the form' AFTER email, ADD INDEX idx_member (", @columnname, ")")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Create contact_chat_messages table (only if it doesn't exist)
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

-- 3. Expand notification type ENUM (add contact_reply if missing)
-- First, check current ENUM values
SET @current_type_enum = (
    SELECT COLUMN_TYPE 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'daybreak' 
      AND TABLE_NAME = 'notifications' 
      AND COLUMN_NAME = 'type'
);

-- Only modify if 'contact_reply' is not in the ENUM
SET @modify_type = IF(
    LOCATE('contact_reply', @current_type_enum) = 0,
    "ALTER TABLE notifications MODIFY type ENUM(
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
    ) NOT NULL",
    "SELECT 'contact_reply already exists in type ENUM' AS Info"
);
PREPARE stmt FROM @modify_type;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Expand notification target_type ENUM (add contact_message if missing)
SET @current_target_enum = (
    SELECT COLUMN_TYPE 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'daybreak' 
      AND TABLE_NAME = 'notifications' 
      AND COLUMN_NAME = 'target_type'
);

SET @modify_target = IF(
    LOCATE('contact_message', @current_target_enum) = 0,
    "ALTER TABLE notifications MODIFY target_type ENUM(
        'article',
        'media',
        'announcement',
        'event',
        'contact_message'
    ) NOT NULL",
    "SELECT 'contact_message already exists in target_type ENUM' AS Info"
);
PREPARE stmt2 FROM @modify_target;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SELECT 'Migration completed successfully!' AS Result;
