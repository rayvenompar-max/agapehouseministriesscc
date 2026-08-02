<?php
declare(strict_types=1);

namespace Repository;

use Model\Member;
use PDO;

class MemberRepository
{
    public function __construct(private readonly PDO $db) {}

    public function findByEmail(string $email): ?Member
    {
        $stmt = $this->db->prepare('SELECT * FROM members WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function findByUsername(string $username): ?Member
    {
        $stmt = $this->db->prepare('SELECT * FROM members WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function findById(int $id): ?Member
    {
        $stmt = $this->db->prepare('SELECT * FROM members WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Fetch a member's public profile by username.
     * Returns only safe, non-sensitive fields (no password, no email).
     */
    public function findPublicByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, display_name, profile_picture, status, created_at
             FROM members WHERE username = :username AND status != :banned LIMIT 1'
        );
        $stmt->execute(['username' => $username, 'banned' => 'banned']);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM members WHERE email = :email');
        $stmt->execute(['email' => $email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM members WHERE username = :username');
        $stmt->execute(['username' => $username]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(string $email, string $username, string $hash, string $displayName): Member
    {
        $stmt = $this->db->prepare(
            'INSERT INTO members (email, username, password, display_name, status)
             VALUES (:email, :username, :password, :display_name, :status)'
        );
        $stmt->execute([
            'email'        => $email,
            'username'     => $username,
            'password'     => $hash,
            'display_name' => $displayName,
            'status'       => 'active',
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE members SET last_login = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $stmt = $this->db->prepare('UPDATE members SET password = :password WHERE id = :id');
        $stmt->execute(['password' => $hash, 'id' => $id]);
    }

    public function updateDisplayName(int $id, string $displayName): void
    {
        $stmt = $this->db->prepare('UPDATE members SET display_name = :display_name WHERE id = :id');
        $stmt->execute(['display_name' => $displayName, 'id' => $id]);
    }

    public function updateEmail(int $id, string $email): void
    {
        $stmt = $this->db->prepare('UPDATE members SET email = :email WHERE id = :id');
        $stmt->execute(['email' => $email, 'id' => $id]);
    }

    public function updateUsername(int $id, string $username): void
    {
        $stmt = $this->db->prepare('UPDATE members SET username = :username WHERE id = :id');
        $stmt->execute(['username' => $username, 'id' => $id]);
    }

    /** Check if email is taken by a different member. */
    public function emailTakenByOther(int $id, string $email): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM members WHERE email = :email AND id != :id');
        $stmt->execute(['email' => $email, 'id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Check if username is taken by a different member. */
    public function usernameTakenByOther(int $id, string $username): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM members WHERE username = :username AND id != :id');
        $stmt->execute(['username' => $username, 'id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function updateProfilePicture(int $id, string $path): void
    {
        $stmt = $this->db->prepare('UPDATE members SET profile_picture = :profile_picture WHERE id = :id');
        $stmt->execute(['profile_picture' => $path, 'id' => $id]);
    }

    // ── Follows ──────────────────────────────────────────────────────────────

    /** Follow another member. Silently ignores if already following. */
    public function follow(int $followerId, int $followingId): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO member_follows (follower_id, following_id) VALUES (:follower, :following)'
        );
        $stmt->execute(['follower' => $followerId, 'following' => $followingId]);
    }

    /** Unfollow another member. */
    public function unfollow(int $followerId, int $followingId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM member_follows WHERE follower_id = :follower AND following_id = :following'
        );
        $stmt->execute(['follower' => $followerId, 'following' => $followingId]);
    }

    /** Check whether $followerId is following $followingId. */
    public function isFollowing(int $followerId, int $followingId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM member_follows WHERE follower_id = :follower AND following_id = :following'
        );
        $stmt->execute(['follower' => $followerId, 'following' => $followingId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** How many members this member is following. */
    public function countFollowing(int $memberId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM member_follows WHERE follower_id = :id'
        );
        $stmt->execute(['id' => $memberId]);
        return (int) $stmt->fetchColumn();
    }

    /** How many members are following this member. */
    public function countFollowers(int $memberId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM member_follows WHERE following_id = :id'
        );
        $stmt->execute(['id' => $memberId]);
        return (int) $stmt->fetchColumn();
    }

    /** Get all active member IDs (for broadcasting notifications). */
    public function getAllActiveMemberIds(): array
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM members WHERE status = :status'
        );
        $stmt->execute(['status' => 'active']);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Search for members by username or display name.
     * Returns only active members, excluding the current user.
     * Limit results to prevent performance issues.
     */
    public function searchMembers(string $query, int $excludeId = 0, int $limit = 10): array
    {
        $searchTerm = '%' . $query . '%';
        
        $sql = 'SELECT id, username, display_name, profile_picture, created_at
                FROM members
                WHERE status = :status
                  AND id != :exclude_id
                  AND (username LIKE :search1 OR display_name LIKE :search2)
                ORDER BY display_name ASC
                LIMIT ' . (int)$limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'status' => 'active',
            'exclude_id' => $excludeId,
            'search1' => $searchTerm,
            'search2' => $searchTerm
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function hydrate(array $row): Member
    {
        return new Member(
            id:             (int)   $row['id'],
            email:                  $row['email'],
            username:               $row['username'],
            password:               $row['password'],
            displayName:            $row['display_name'],
            profilePicture:         $row['profile_picture'] ?? null,
            status:                 $row['status'],
            lastLogin:              $row['last_login'] ?? null,
            createdAt:              $row['created_at'],
        );
    }
}
