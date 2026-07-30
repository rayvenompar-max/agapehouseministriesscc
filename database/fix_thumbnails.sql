-- ============================================================
-- Fix thumbnails: clear stored thumbnail values so the app
-- auto-derives them from video_url.
--
-- After running this, update video_url values in each row
-- with real working YouTube URLs and thumbnails will
-- automatically resolve to the correct YouTube thumbnail.
--
-- Run in phpMyAdmin: Database > SQL > paste & execute
-- Or via CLI: mysql -u root daybreak < fix_thumbnails.sql
-- ============================================================

USE daybreak;

-- Clear all stored thumbnails so they are derived from video_url automatically
UPDATE media SET thumbnail = '';

-- ============================================================
-- Then update video_url with your real YouTube video URLs.
-- Example (replace VIDEO_ID with a real 11-char YouTube ID):
--
-- UPDATE media SET video_url = 'https://www.youtube.com/watch?v=VIDEO_ID'
-- WHERE title = 'What It Means to Abide';
-- ============================================================
