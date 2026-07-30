<?php
/**
 * Repository\AdminRepository
 */
declare(strict_types=1);

namespace Repository;

use Model\Admin;
use PDO;

class AdminRepository
{
    public function __construct(private readonly PDO $db) {}

    public function findByUsername(string $username): ?Admin
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM admins WHERE username = :username LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE admins SET last_login = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $stmt = $this->db->prepare(
            'UPDATE admins SET password = :password WHERE id = :id'
        );
        $stmt->execute(['password' => $hash, 'id' => $id]);
    }

    public function create(string $username, string $hash, string $displayName): Admin
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admins (username, password, display_name, role)
             VALUES (:username, :password, :display_name, :role)'
        );
        $stmt->execute([
            'username'     => $username,
            'password'     => $hash,
            'display_name' => $displayName,
            'role'         => 'admin',
        ]);
        return $this->findByUsername($username);
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM admins WHERE username = :username'
        );
        $stmt->execute(['username' => $username]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function hydrate(array $row): Admin
    {
        return new Admin(
            id:          (int)  $row['id'],
            username:           $row['username'],
            password:           $row['password'],
            displayName:        $row['display_name'],
            role:               $row['role'],
            lastLogin:          $row['last_login']  ?? null,
            createdAt:          $row['created_at'],
        );
    }
}
