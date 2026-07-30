-- =============================================================
-- Members table — run once to set up member authentication
-- =============================================================

USE daybreak;

CREATE TABLE IF NOT EXISTS members (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(255) NOT NULL UNIQUE,
    username     VARCHAR(60)  NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
    display_name VARCHAR(120) NOT NULL DEFAULT '',
    status       ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
    last_login   DATETIME     NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email    (email),
    INDEX idx_username (username),
    INDEX idx_status   (status)
) ENGINE=InnoDB;
