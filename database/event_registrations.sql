-- ---------------------------------------------------------------
-- event_registrations
-- Tracks which member registered for which event and how (online/in-person).
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS event_registrations (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id    INT UNSIGNED NOT NULL,
    member_id   INT UNSIGNED NOT NULL,
    join_type   ENUM('online','in_person') NOT NULL DEFAULT 'in_person',
    registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_event_member (event_id, member_id),
    KEY idx_event_id  (event_id),
    KEY idx_member_id (member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
