    -- Add member_id column to media table if it doesn't exist
    -- This allows tracking which member posted a video/media

    USE daybreak;

    -- Check if column exists, add if not
    SET @dbname = DATABASE();
    SET @tablename = 'media';
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
    CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " INT UNSIGNED DEFAULT NULL AFTER featured")
    ));
    PREPARE alterIfNotExists FROM @preparedStatement;
    EXECUTE alterIfNotExists;
    DEALLOCATE PREPARE alterIfNotExists;

    -- Add index for member_id
    SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
        WHERE
        (table_name = @tablename)
        AND (table_schema = @dbname)
        AND (index_name = 'idx_member_id')
    ) > 0,
    "SELECT 1",
    CONCAT("ALTER TABLE ", @tablename, " ADD INDEX idx_member_id (member_id)")
    ));
    PREPARE alterIfNotExists FROM @preparedStatement;
    EXECUTE alterIfNotExists;
    DEALLOCATE PREPARE alterIfNotExists;
