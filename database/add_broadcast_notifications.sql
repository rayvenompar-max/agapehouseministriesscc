-- =============================================================
-- Migration: Add broadcast notification support
-- Run this once against the daybreak database.
-- =============================================================

USE daybreak;

-- 1. Allow actor_id = 0 (system/broadcast sender)
ALTER TABLE notifications
    MODIFY actor_id INT UNSIGNED NOT NULL DEFAULT 0
    COMMENT 'Member who triggered the action; 0 = system broadcast';

-- 2. Expand the type ENUM to include broadcast types
ALTER TABLE notifications
    MODIFY type ENUM(
        'like',
        'comment',
        'share',
        'comment_like',
        'comment_reply',
        'follow',
        'follow_back',
        'new_event',
        'new_announcement'
    ) NOT NULL;

-- 3. Expand target_type ENUM to include event
ALTER TABLE notifications
    MODIFY target_type ENUM(
        'article',
        'media',
        'announcement',
        'event'
    ) NOT NULL;

-- 4. Remove any stale broadcast notifications that were incorrectly
--    stored with a real actor_id (from before this fix).
DELETE FROM notifications
WHERE type IN ('new_event', 'new_announcement') AND actor_id != 0;
