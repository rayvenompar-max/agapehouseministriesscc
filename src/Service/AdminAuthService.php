<?php
/**
 * Service\AdminAuthService
 *
 * Handles admin authentication, session management, and password changes.
 */
declare(strict_types=1);

namespace Service;

use Repository\AdminRepository;

class AdminAuthService
{
    private const SESSION_KEY = 'admin';

    public function __construct(private readonly AdminRepository $repo) {}

    /**
     * Attempt login. Returns true on success, false on failure.
     */
    public function login(string $username, string $password): bool
    {
        $username = trim($username);

        if ($username === '' || $password === '') {
            return false;
        }

        $admin = $this->repo->findByUsername($username);

        if (!$admin || !password_verify($password, $admin->password)) {
            // Consistent timing to prevent username enumeration
            password_verify('dummy', '$2y$12$invalid.hash.padding.here.123456789012345678901234');
            return false;
        }

        // Rehash if bcrypt cost has changed
        if (password_needs_rehash($admin->password, PASSWORD_BCRYPT, ['cost' => 12])) {
            $this->repo->updatePassword($admin->id, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));
        }

        $this->repo->updateLastLogin($admin->id);

        // Regenerate session ID to prevent session fixation
        // Write data first, then regenerate (keeps data in new session)
        $_SESSION[self::SESSION_KEY] = $admin->toSessionArray();
        session_regenerate_id(false); // false = keep old session file briefly

        return true;
    }

    /**
     * Log out the current admin.
     */
    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        unset($_SESSION['admin_logged_in']); // clear legacy key
        session_regenerate_id(true);
    }

    /**
     * Check if a valid admin session exists.
     */
    public function isLoggedIn(): bool
    {
        // New session key
        if (!empty($_SESSION[self::SESSION_KEY]['id'])) {
            return true;
        }
        // Legacy key from old auth system — honour it during transition
        if (!empty($_SESSION['admin_logged_in'])) {
            return true;
        }
        return false;
    }

    /**
     * Return the current admin's session data, or null.
     */
    public function current(): ?array
    {
        if (!empty($_SESSION[self::SESSION_KEY])) {
            return $_SESSION[self::SESSION_KEY];
        }
        // Legacy session — synthesize a minimal array
        if (!empty($_SESSION['admin_logged_in'])) {
            return ['username' => 'admin', 'display_name' => 'Admin', 'role' => 'admin'];
        }
        return null;
    }

    /**
     * Require authentication — redirect to login if not logged in.
     */
    public function requireLogin(string $loginUrl): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    /**
     * Register a new admin account. Returns true on success, false if username exists.
     */
    public function register(string $username, string $password, string $displayName): bool
    {
        $username    = trim($username);
        $displayName = trim($displayName);

        if ($username === '' || $password === '' || $displayName === '') {
            return false;
        }

        if ($this->repo->usernameExists($username)) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $admin = $this->repo->create($username, $hash, $displayName);

        // Auto-login after registration
        session_regenerate_id(false);
        $_SESSION[self::SESSION_KEY] = $admin->toSessionArray();

        return true;
    }
}
