-- ================================================================
-- Content Approval Workflow Migration
-- Run once: mysql -u root daybreak < add_content_approval.sql
-- ================================================================

USE daybreak;

-- Add status column to media (member-submitted videos go pending)
ALTER TABLE media
  ADD COLUMN IF NOT EXISTS status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'
  AFTER video_url;

-- Existing media (created before this migration) should be treated as approved
UPDATE media SET status = 'approved' WHERE status = 'pending';

-- Add status column to articles (member-submitted articles go pending)
ALTER TABLE articles
  ADD COLUMN IF NOT EXISTS status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'
  AFTER body;

-- Existing articles should be treated as approved
UPDATE articles SET status = 'approved' WHERE status = 'pending';

-- Add index for fast pending-queue queries
ALTER TABLE media    ADD INDEX IF NOT EXISTS idx_status (status);
ALTER TABLE articles ADD INDEX IF NOT EXISTS idx_status (status);
