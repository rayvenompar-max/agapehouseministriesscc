<?php
declare(strict_types=1);

namespace Service;

use Repository\MemberRepository;

class MemberAuthService
{
    private const SESSION_KEY = 'member';

    public function __construct(private readonly MemberRepository $repo) {}

    /** Login via email or username. Returns true on success. */
    public function login(string $identifier, string $password): bool
    {
        $identifier = trim($identifier);
        if ($identifier === '' || $password === '') return false;

        // Support login by email or username
        $member = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $this->repo->findByEmail($identifier)
            : $this->repo->findByUsername($identifier);

        if (!$member || $member->status === 'banned') {
            password_verify('dummy', '$2y$12$invalid.hash.padding.here.123456789012345678901234');
            return false;
        }

        if (!password_verify($password, $member->password)) {
            return false;
        }

        if (password_needs_rehash($member->password, PASSWORD_BCRYPT, ['cost' => 12])) {
            $this->repo->updatePassword($member->id, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));
        }

        $this->repo->updateLastLogin($member->id);
        $_SESSION[self::SESSION_KEY] = $member->toSessionArray();
        session_regenerate_id(false);

        return true;
    }

    /** Register a new member. Returns true on success. */
    public function register(string $email, string $username, string $password, string $displayName): bool|string
    {
        $email       = trim(strtolower($email));
        $username    = trim($username);
        $displayName = trim($displayName);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Invalid email address.';
        if ($this->repo->emailExists($email))            return 'An account with that email already exists.';
        if ($this->repo->usernameExists($username))      return 'That username is already taken.';

        $hash   = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $member = $this->repo->create($email, $username, $hash, $displayName);

        $_SESSION[self::SESSION_KEY] = $member->toSessionArray();
        session_regenerate_id(false);

        return true;
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }

    public function isLoggedIn(): bool
    {
        return !empty($_SESSION[self::SESSION_KEY]['id']);
    }

    public function current(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public function requireLogin(string $loginUrl): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: ' . $loginUrl);
            exit;
        }
    }
}
