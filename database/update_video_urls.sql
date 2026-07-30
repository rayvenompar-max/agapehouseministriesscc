-- ============================================================
-- Update media rows with real YouTube URLs
-- Run in phpMyAdmin: Database > SQL > paste & execute
-- Or via CLI: mysql -u root daybreak < update_video_urls.sql
-- ============================================================
--
-- These are publicly available BibleProject videos on YouTube
-- used as placeholder content. Replace with your own church's
-- video URLs before going live.
--
-- Thumbnails are left empty ('') — the app auto-derives them
-- from video_url so you never need to keep them in sync.
-- ============================================================

USE daybreak;

UPDATE media SET video_url = 'https://www.youtube.com/watch?v=G-2e9mMf7E8', thumbnail = '' WHERE title = 'What It Means to Abide';
UPDATE media SET video_url = 'https://www.youtube.com/watch?v=Y71r-T98E2Q', thumbnail = '' WHERE title = 'Pruned to Grow';
UPDATE media SET video_url = 'https://www.youtube.com/watch?v=oNNZO9i1Gjc', thumbnail = '' WHERE title = 'Enough for Today';
UPDATE media SET video_url = 'https://www.youtube.com/watch?v=kE6SZ1ogOVU', thumbnail = '' WHERE title = 'Mara''s Story';
UPDATE media SET video_url = 'https://www.youtube.com/watch?v=Z-17KxpjL0Q', thumbnail = '' WHERE title = 'Deep Water';
UPDATE media SET video_url = 'https://www.youtube.com/watch?v=oNpTha80yyE', thumbnail = '' WHERE title = 'Live from Main Site';
UPDATE media SET video_url = 'https://www.youtube.com/watch?v=_TzdEPuqgQg', thumbnail = '' WHERE title = 'When Prayer Feels Silent';
