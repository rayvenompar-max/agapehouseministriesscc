<?php
/**
 * Repository\PrayerRepository
 */
declare(strict_types=1);

namespace Repository;

use Model\PrayerRequest;
use PDO;

class PrayerRepository
{
    public function __construct(private readonly PDO $db) {}

    /** Only return approved requests for the public wall. */
    public function findApproved(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM prayer_requests WHERE status = 'approved' ORDER BY created_at DESC"
        );
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /** Return pending requests for moderation. */
    public function findPending(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM prayer_requests WHERE status = 'pending' ORDER BY created_at ASC"
        );
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function findById(int $id): ?PrayerRequest
    {
        $stmt = $this->db->prepare('SELECT * FROM prayer_requests WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function create(string $name, string $category, string $body): PrayerRequest
    {
        $stmt = $this->db->prepare(
            'INSERT INTO prayer_requests (name, category, body, pray_count, status, created_at)
             VALUES (:name, :category, :body, 0, :status, NOW())'
        );
        $stmt->execute([
            'name'     => $name,
            'category' => $category,
            'body'     => $body,
            'status'   => 'approved',   // auto-approved, posts immediately
        ]);

        return $this->findById((int) $this->db->lastInsertId());
    }

    /** Increment the pray count for one request. */
    public function incrementPrayCount(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE prayer_requests SET pray_count = pray_count + 1 WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /** Update status to approved or rejected. */
    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['approved', 'rejected'], true)) {
            throw new \InvalidArgumentException('Invalid status value.');
        }
        $stmt = $this->db->prepare(
            'UPDATE prayer_requests SET status = :status WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'status' => $status]);
        return $stmt->rowCount() > 0;
    }

    private function hydrate(array $row): PrayerRequest
    {
        return new PrayerRequest(
            id:         (int)  $row['id'],
            name:              $row['name'],
            category:          $row['category'],
            body:              $row['body'],
            prayCount:  (int)  $row['pray_count'],
            status:            $row['status'],
            createdAt:         $row['created_at'],
        );
    }
}
