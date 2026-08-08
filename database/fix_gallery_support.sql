        -- =============================================================
        -- Fix Gallery Support - Add gallery to comments and post_likes
        -- =============================================================
        -- This script adds 'gallery' support to both comments and likes
        -- Run this once in phpMyAdmin or via CLI:
        -- mysql -u root daybreak < fix_gallery_support.sql
        -- =============================================================

        USE daybreak;

        -- Add 'gallery' to comments target_type ENUM
        ALTER TABLE comments 
        MODIFY COLUMN target_type ENUM('media','article','announcement','gallery') NOT NULL;

        -- Add 'gallery' to post_likes target_type ENUM  
        ALTER TABLE post_likes 
        MODIFY COLUMN target_type ENUM('article','media','announcement','gallery') NOT NULL;

        -- Verify the changes
        SELECT 'Comments table updated' AS status;
        SHOW COLUMNS FROM comments LIKE 'target_type';

        SELECT 'Post_likes table updated' AS status;
        SHOW COLUMNS FROM post_likes LIKE 'target_type';

        -- Show sample data to verify
        SELECT COUNT(*) as comment_count FROM comments WHERE target_type = 'gallery';
        SELECT COUNT(*) as like_count FROM post_likes WHERE target_type = 'gallery';
