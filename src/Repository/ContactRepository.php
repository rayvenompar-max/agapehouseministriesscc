<?php
/**
 * Repository\ContactRepository
 */
declare(strict_types=1);

namespace Repository;

use Model\ContactMessage;
use PDO;

class ContactRepository
{
    public function __construct(private readonly PDO $db) {}

    public function create(
        string $name,
        string $email,
        string $reason,
        string $message
    ): ContactMessage {
        $stmt = $this->db->prepare(
            'INSERT INTO contact_messages (name, email, reason, message, status, created_at)
             VALUES (:name, :email, :reason, :message, :status, NOW())'
        );
        $stmt->execute([
            'name'    => $name,
            'email'   => $email,
            'reason'  => $reason,
            'message' => $message,
            'status'  => 'unread',
        ]);

        return $this->findById((int) $this->db->lastInsertId());
    }

    public function findById(int $id): ?ContactMessage
    {
        $stmt = $this->db->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    private function hydrate(array $row): ContactMessage
    {
        return new ContactMessage(
            id:        (int) $row['id'],
            name:            $row['name'],
            email:           $row['email'],
            reason:          $row['reason'],
            message:         $row['message'],
            status:          $row['status'],
            createdAt:       $row['created_at'],
        );
    }
}
