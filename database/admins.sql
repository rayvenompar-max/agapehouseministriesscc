 -- =============================================================
-- Admins table — run once to set up admin authentication
-- =============================================================

USE daybreak;

CREATE TABLE IF NOT EXISTS admins (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(60)  NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
    display_name VARCHAR(120) NOT NULL DEFAULT '',
    role         ENUM('super_admin','admin') NOT NULL DEFAULT 'admin',
    last_login   DATETIME     NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB;

-- Default admin account: username=admin, password=admin123
-- Change the password immediately after first login!
INSERT INTO admins (username, password, display_name, role)
VALUES (
    'admin',
    '$2y$12$SUdYzcgyCoV0BwW9ZxtivevL81y0cUPOC0FEviL99mxd0kogz3Iwe', -- admin123
    'Administrator',
    'super_admin'
)
ON DUPLICATE KEY UPDATE id = id;
