-- Add member_id to announcements so poster ownership can be tracked
-- Safe to run multiple times

USE daybreak;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = 'daybreak' AND TABLE_NAME = 'announcements' AND COLUMN_NAME = 'member_id');
SET @sql = IF(@col = 0,
    'ALTER TABLE announcements ADD COLUMN member_id INT UNSIGNED NULL DEFAULT NULL AFTER posted_by',
    "SELECT 'announcements.member_id already exists' AS note");
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx = (SELECT COUNT(*) FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = 'daybreak' AND TABLE_NAME = 'announcements' AND INDEX_NAME = 'idx_member_id');
SET @sql2 = IF(@idx = 0,
    'ALTER TABLE announcements ADD INDEX idx_member_id (member_id)',
    "SELECT 'index already exists' AS note");
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;
