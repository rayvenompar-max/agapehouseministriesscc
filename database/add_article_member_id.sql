-- Add member_id to articles so poster profile pictures can be resolved
-- Safe to run multiple times

USE daybreak;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = 'daybreak' AND TABLE_NAME = 'articles' AND COLUMN_NAME = 'member_id');
SET @sql = IF(@col = 0,
    'ALTER TABLE articles ADD COLUMN member_id INT UNSIGNED NULL DEFAULT NULL AFTER posted_by',
    "SELECT 'articles.member_id already exists' AS note");
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
