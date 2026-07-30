-- Add posted_by column to articles, announcements and media
-- Safe to run multiple times — checks if column exists first

USE daybreak;

-- articles
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = 'daybreak' AND TABLE_NAME = 'articles' AND COLUMN_NAME = 'posted_by');
SET @sql = IF(@col = 0,
    "ALTER TABLE articles ADD COLUMN posted_by VARCHAR(120) NOT NULL DEFAULT 'Agape House' AFTER read_minutes",
    "SELECT 'articles.posted_by already exists' AS note");
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- announcements
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = 'daybreak' AND TABLE_NAME = 'announcements' AND COLUMN_NAME = 'posted_by');
SET @sql = IF(@col = 0,
    "ALTER TABLE announcements ADD COLUMN posted_by VARCHAR(120) NOT NULL DEFAULT 'Agape House' AFTER category",
    "SELECT 'announcements.posted_by already exists' AS note");
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- media
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = 'daybreak' AND TABLE_NAME = 'media' AND COLUMN_NAME = 'posted_by');
SET @sql = IF(@col = 0,
    "ALTER TABLE media ADD COLUMN posted_by VARCHAR(120) NOT NULL DEFAULT 'Agape House' AFTER series",
    "SELECT 'media.posted_by already exists' AS note");
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
