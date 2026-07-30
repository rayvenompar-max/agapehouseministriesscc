-- =============================================================
-- Add Admin User
-- Run this SQL to create a new admin account
-- =============================================================

USE daybreak;

-- Insert a new admin user
-- Default credentials: username=admin, password=admin123
-- 
-- TO CHANGE THE PASSWORD:
-- 1. Use a bcrypt hash generator (cost=12) or
-- 2. Run this PHP snippet:
--    echo password_hash('YourNewPassword', PASSWORD_BCRYPT, ['cost' => 12]);
--
INSERT INTO admins (username, password, display_name, role)
VALUES (
    'admin',
    '$2y$12$SUdYzcgyCoV0BwW9ZxtivevL81y0cUPOC0FEviL99mxd0kogz3Iwe', -- admin123
    'Administrator',
    'super_admin'
)
ON DUPLICATE KEY UPDATE id = id;

-- To add a different admin user, uncomment and modify this:
-- INSERT INTO admins (username, password, display_name, role)
-- VALUES (
--     'yourname',
--     '$2y$12$SUdYzcgyCoV0BwW9ZxtivevL81y0cUPOC0FEviL99mxd0kogz3Iwe', -- admin123
--     'Your Display Name',
--     'admin'
-- );

-- Verify the admin was created:
SELECT id, username, display_name, role, created_at 
FROM admins 
ORDER BY created_at DESC;
