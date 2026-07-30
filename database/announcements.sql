-- =============================================================
-- Daybreak — Announcements Table
-- Run in phpMyAdmin or: mysql -u root daybreak < announcements.sql
-- =============================================================

USE daybreak;

CREATE TABLE IF NOT EXISTS announcements (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    body         TEXT         NOT NULL,
    category     ENUM('Ministry','Events','Community','Urgent') NOT NULL DEFAULT 'Ministry',
    is_pinned    TINYINT(1)   NOT NULL DEFAULT 0,
    published_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pinned    (is_pinned),
    INDEX idx_published (published_at),
    INDEX idx_category  (category)
) ENGINE=InnoDB;

-- Seed data
INSERT INTO announcements (title, body, category, is_pinned, published_at) VALUES
('Building closed this Friday for facility upgrades',
 'The main campus will be closed Friday, July 31 for scheduled electrical work. All Friday programs move online — check your email for the stream link.',
 'Urgent', 1, NOW()),

('Summer series "Roots" begins this Sunday',
 'Two services, 9am and 11am. Childcare available for both. Come early — parking fills up fast on series launch weekends.',
 'Events', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),

('Volunteers needed for Community Outreach — August 5',
 'We are looking for 20 volunteers to help with meals and prayer at the Pantalan waterfront. Sign up at the Connect desk or reply to this announcement.',
 'Community', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),

('New Small Groups starting this September',
 'Ministry teams are forming new small groups for the fall season. Signup sheets are available at all campuses. Groups meet weekly on Tuesday and Thursday evenings.',
 'Ministry', 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),

('Baptism Sunday — August 22',
 'If you are considering baptism, join the info session on August 22 at 10am in the Main Site. This is open to all ages.',
 'Events', 0, DATE_SUB(NOW(), INTERVAL 4 DAY)),

('Online giving now available',
 'You can now give securely online through our Connect page. Tithes, offerings, and mission support are all available. Thank you for your generosity.',
 'Ministry', 0, DATE_SUB(NOW(), INTERVAL 5 DAY));
