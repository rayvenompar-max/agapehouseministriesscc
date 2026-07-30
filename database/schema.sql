-- =============================================================
-- Daybreak — Database Schema
-- Run once in phpMyAdmin or: mysql -u root daybreak < schema.sql
-- =============================================================

CREATE DATABASE IF NOT EXISTS daybreak
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE daybreak;

-- ---------------------------------------------------------------
-- media
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS media (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255)  NOT NULL,
    description  TEXT          NOT NULL,
    type         ENUM('sermon','devotional','testimony','worship') NOT NULL,
    series       VARCHAR(120)  NOT NULL DEFAULT '',
    duration     INT UNSIGNED  NOT NULL DEFAULT 0 COMMENT 'seconds',
    thumbnail    VARCHAR(500)  NOT NULL DEFAULT '',
    video_url    VARCHAR(500)  NOT NULL DEFAULT '',
    featured     TINYINT(1)    NOT NULL DEFAULT 0,
    published_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type       (type),
    INDEX idx_featured   (featured),
    INDEX idx_published  (published_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- articles
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS articles (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    excerpt      TEXT         NOT NULL,
    body         LONGTEXT     NOT NULL,
    read_minutes TINYINT UNSIGNED NOT NULL DEFAULT 5,
    published_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_published (published_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- prayer_requests
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS prayer_requests (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL DEFAULT 'Anonymous',
    category    ENUM('Healing','Family','Guidance','Provision','Thanksgiving') NOT NULL,
    body        TEXT         NOT NULL,
    pray_count  INT UNSIGNED NOT NULL DEFAULT 0,
    status      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status     (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- events
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title          VARCHAR(255) NOT NULL,
    description    TEXT         NOT NULL,
    event_date     DATE         NOT NULL,
    start_time     TIME         NOT NULL,
    location       VARCHAR(255) NOT NULL DEFAULT '',
    has_livestream TINYINT(1)   NOT NULL DEFAULT 0,
    is_recurring   TINYINT(1)   NOT NULL DEFAULT 0,
    recur_day      VARCHAR(20)  NOT NULL DEFAULT '',
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_date  (event_date),
    INDEX idx_is_recurring(is_recurring)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- donations
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS donations (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    donor_name   VARCHAR(120)   NOT NULL DEFAULT 'Anonymous',
    donor_email  VARCHAR(255)   NOT NULL,
    amount       DECIMAL(10,2)  NOT NULL,
    currency     CHAR(3)        NOT NULL DEFAULT 'USD',
    frequency    ENUM('one_time','monthly') NOT NULL DEFAULT 'one_time',
    tier         VARCHAR(50)    NOT NULL DEFAULT 'custom',
    status       ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
    created_at   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status    (status),
    INDEX idx_frequency (frequency)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- contact_messages
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    reason     VARCHAR(80)  NOT NULL,
    message    TEXT         NOT NULL,
    status     ENUM('unread','read','replied') NOT NULL DEFAULT 'unread',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB;


-- =============================================================
-- Seed data (mirrors the static HTML content)
-- =============================================================

INSERT INTO media (title, description, type, series, duration, featured, published_at, video_url) VALUES
('What It Means to Abide',    'A message on John 15 — staying connected to the vine when life pulls you elsewhere.',  'sermon',     'Roots',           2280, 1, NOW(),                              'https://www.youtube.com/watch?v=5IATfBFsyNs'),
('Pruned to Grow',            'Why hard seasons often come right before fruit.',                                       'sermon',     'Roots · Pt. 3',   1440, 0, DATE_SUB(NOW(), INTERVAL 7 DAY),    'https://www.youtube.com/watch?v=7ltmb6SdGj8'),
('Enough for Today',          'A short word on manna and trusting God one day at a time.',                             'devotional', 'Daily Devotional', 360, 0, DATE_SUB(NOW(), INTERVAL 1 DAY),    'https://www.youtube.com/watch?v=pVgFb4hWCGc'),
('Mara\'s Story',             'From a hospital waiting room to a changed life.',                                       'testimony',  'Testimony',        660, 0, DATE_SUB(NOW(), INTERVAL 2 DAY),    'https://www.youtube.com/watch?v=KfuKMnLSWpc'),
('Deep Water',                'Building a life on something that doesn\'t move.',                                      'sermon',     'Roots · Pt. 2',   1860, 0, DATE_SUB(NOW(), INTERVAL 14 DAY),   'https://www.youtube.com/watch?v=SqchgHx9U0Q'),
('Live from Main Site',       'A full worship set recorded live, uncut.',                                              'worship',    'Worship Night',   2700, 0, DATE_SUB(NOW(), INTERVAL 21 DAY),   'https://www.youtube.com/watch?v=FgGAqS-5s2k'),
('When Prayer Feels Silent',  'What to do when heaven seems quiet.',                                                   'devotional', 'Daily Devotional', 480, 0, DATE_SUB(NOW(), INTERVAL 3 DAY),    'https://www.youtube.com/watch?v=AyOHXI3sY8w');

-- Update existing media rows with video URLs (run if table already exists)
-- UPDATE media SET video_url = 'https://www.youtube.com/watch?v=5IATfBFsyNs' WHERE title = 'What It Means to Abide';
-- UPDATE media SET video_url = 'https://www.youtube.com/watch?v=7ltmb6SdGj8' WHERE title = 'Pruned to Grow';
-- UPDATE media SET video_url = 'https://www.youtube.com/watch?v=pVgFb4hWCGc' WHERE title = 'Enough for Today';
-- UPDATE media SET video_url = 'https://www.youtube.com/watch?v=KfuKMnLSWpc' WHERE title = 'Mara''s Story';
-- UPDATE media SET video_url = 'https://www.youtube.com/watch?v=SqchgHx9U0Q' WHERE title = 'Deep Water';
-- UPDATE media SET video_url = 'https://www.youtube.com/watch?v=FgGAqS-5s2k' WHERE title = 'Live from Main Site';
-- UPDATE media SET video_url = 'https://www.youtube.com/watch?v=AyOHXI3sY8w' WHERE title = 'When Prayer Feels Silent';

INSERT INTO articles (title, excerpt, body, read_minutes, published_at) VALUES
('Enough for Today',           'On manna, anxiety, and the discipline of asking only for today\'s bread.',                        'Full article body here...', 5, NOW()),
('The Waiting Isn\'t Wasted',  'A look at the four hundred silent years before the Gospels begin.',                               'Full article body here...', 7, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Small Enough to Notice',     'What the mustard seed says about the size of what you\'re carrying right now.',                   'Full article body here...', 4, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('A Different Kind of Rest',   'Sabbath as resistance — why stopping is itself an act of trust.',                                  'Full article body here...', 6, DATE_SUB(NOW(), INTERVAL 3 DAY));

INSERT INTO prayer_requests (name, category, body, pray_count, status) VALUES
('Anonymous', 'Healing',      'My father goes in for surgery on Thursday. Praying for the surgeons\' hands and for his peace.', 128, 'approved'),
('Josefina',  'Guidance',     'Trying to decide whether to take a new job across the country. Asking for clarity, not just a good offer.', 64, 'approved'),
('Anonymous', 'Thanksgiving', 'Six months sober today. Thank you all for praying — it worked.', 302, 'approved'),
('Marcus',    'Family',       'My daughter and I haven\'t spoken in a year. Praying for a way back to each other.', 91, 'approved');

INSERT INTO events (title, description, event_date, start_time, location, has_livestream, is_recurring, recur_day) VALUES
('Main Site Worship Service',          'In person + livestream service',               CURDATE(), '09:00:00', 'Main Site',     1, 1, 'Sunday'),
('Midweek Bible Study — Book of Romans','Livestream only',                              CURDATE(), '19:00:00', 'Online',        1, 1, 'Wednesday'),
('Young Adults Prayer Night',           'Site 2 chapel + livestream',                  CURDATE(), '20:30:00', 'Site 2 Chapel', 1, 1, 'Friday'),
('Worship Night: Wide Open',            'An evening of music and open prayer at Main Site.', '2026-08-14', '19:00:00', 'Main Site', 1, 0, ''),
('Baptism Sunday Prep',                 'Info session for anyone considering baptism.', '2026-08-22', '10:00:00', 'Main Site', 0, 0, ''),
('Community Outreach — Pantalan',       'Meals, prayer, and conversation along the waterfront.', '2026-09-05', '08:00:00', 'Pantalan Waterfront', 0, 0, '');
