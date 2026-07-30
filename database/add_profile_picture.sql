-- Add profile_picture column to members table
-- Run once against the daybreak database

USE daybreak;

ALTER TABLE members
    ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(500) NULL DEFAULT NULL
        COMMENT 'Relative path to uploaded avatar, e.g. /public/uploads/avatars/abc123.jpg'
    AFTER display_name;
