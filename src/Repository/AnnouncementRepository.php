<?php
declare(strict_types=1);

namespace Repository;

use Model\Announcement;
use PDO;

class AnnouncementRepository
{
    public function __construct(private readonly PDO $db) {}

    /** Return all announcements ordered: pinned first, then newest first. */
    public function findAll(?string $category = null): array
    {
        $sql    = 'SELECT a.*,
                          (SELECT COUNT(*) FROM comments c WHERE c.target_type = \'announcement\' AND c.target_id = a.id) AS comment_count
                   FROM announcements a';
        $params = [];

        if ($category !== null) {
            $sql    .= ' WHERE a.category = :category';
            $params[':category'] = $category;
        }

        $sql .= ' ORDER BY a.is_pinned DESC, a.published_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map(
            fn(array $row) => Announcement::fromRow($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /** Return the single pinned announcement (if any). */
    public function findPinned(): ?Announcement
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM announcements WHERE is_pinned = 1 ORDER BY published_at DESC LIMIT 1'
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Announcement::fromRow($row) : null;
    }

    public function findById(int $id): ?Announcement
    {
        $stmt = $this->db->prepare('SELECT * FROM announcements WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Announcement::fromRow($row) : null;
    }

    public function create(array $data): Announcement
    {
        $stmt = $this->db->prepare(
            'INSERT INTO announcements (title, body, category, is_pinned, published_at, posted_by, member_id)
             VALUES (:title, :body, :category, :is_pinned, :published_at, :posted_by, :member_id)'
        );
        $stmt->execute([
            ':title'        => $data['title'],
            ':body'         => $data['body'],
            ':category'     => $data['category']     ?? 'Ministry',
            ':is_pinned'    => (int) ($data['is_pinned'] ?? 0),
            ':published_at' => $data['published_at'] ?? date('Y-m-d H:i:s'),
            ':posted_by'    => $data['posted_by']    ?? 'Agape House',
            ':member_id'    => isset($data['member_id']) ? (int) $data['member_id'] : null,
        ]);

        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): ?Announcement
    {
        $fields = [];
        $params = [':id' => $id];

        $allowed = ['title', 'body', 'category', 'is_pinned', 'published_at'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[]         = "{$field} = :{$field}";
                $params[":{$field}"] = $field === 'is_pinned' ? (int) $data[$field] : $data[$field];
            }
        }

        if (empty($fields)) {
            return $this->findById($id);
        }

        $sql = 'UPDATE announcements SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $this->db->prepare($sql)->execute($params);
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM announcements WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM announcements')->fetchColumn();
    }
}
