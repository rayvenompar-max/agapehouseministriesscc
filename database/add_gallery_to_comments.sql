-- =============================================================
-- Add 'gallery' to comments target_type ENUM
-- =============================================================
-- This allows gallery items to have comments
-- Run once in phpMyAdmin or: mysql -u root daybreak < add_gallery_to_comments.sql
-- =============================================================

USE daybreak;

-- Modify the target_type column to include 'gallery'
ALTER TABLE comments 
MODIFY COLUMN target_type ENUM('media','article','announcement','gallery') NOT NULL;
