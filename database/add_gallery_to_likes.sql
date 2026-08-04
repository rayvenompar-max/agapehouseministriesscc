-- =============================================================
-- Add 'gallery' to post_likes target_type ENUM
-- This allows gallery items to be liked
-- Run once in phpMyAdmin or: mysql -u root daybreak < add_gallery_to_likes.sql
-- =============================================================

USE daybreak;

-- Modify the target_type column to include 'gallery'
ALTER TABLE post_likes 
MODIFY COLUMN target_type ENUM('article','media','announcement','gallery') NOT NULL;
