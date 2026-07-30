<?php
/**
 * Repository\ArticleRepository
 */
declare(strict_types=1);

namespace Repository;

use Model\Article;
use PDO;

class ArticleRepository
{
    public function __construct(private readonly PDO $db) {}

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT a.*,
                    COALESCE(
                        (SELECT mem.profile_picture FROM members mem WHERE mem.id = a.member_id LIMIT 1),
                        (SELECT mem.profile_picture FROM members mem WHERE mem.display_name = a.posted_by LIMIT 1)
                    ) AS poster_picture,
                    COALESCE(
                        (SELECT mem.username FROM members mem WHERE mem.id = a.member_id LIMIT 1),
                        (SELECT mem.username FROM members mem WHERE mem.display_name = a.posted_by LIMIT 1)
                    ) AS poster_username,
                    (SELECT COUNT(*) FROM comments c WHERE c.target_type = \'article\' AND c.target_id = a.id) AS comment_count
             FROM articles a
             ORDER BY a.published_at DESC'
        );
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function findById(int $id): ?Article
    {
        $stmt = $this->db->prepare(
            'SELECT a.*,
                    COALESCE(
                        (SELECT mem.profile_picture FROM members mem WHERE mem.id = a.member_id LIMIT 1),
                        (SELECT mem.profile_picture FROM members mem WHERE mem.display_name = a.posted_by LIMIT 1)
                    ) AS poster_picture,
                    COALESCE(
                        (SELECT mem.username FROM members mem WHERE mem.id = a.member_id LIMIT 1),
                        (SELECT mem.username FROM members mem WHERE mem.display_name = a.posted_by LIMIT 1)
                    ) AS poster_username
             FROM articles a
             WHERE a.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function insert(
        string  $title,
        string  $excerpt,
        string  $body,
        int     $readMinutes,
        ?string $publishedAt,
        string  $postedBy = 'Agape House',
        ?int    $memberId = null,
    ): ?Article {
        $published = $publishedAt
            ? date('Y-m-d H:i:s', strtotime($publishedAt))
            : date('Y-m-d H:i:s');

        $stmt = $this->db->prepare(
            'INSERT INTO articles (title, excerpt, body, read_minutes, published_at, posted_by, member_id)
             VALUES (:title, :excerpt, :body, :read_minutes, :published_at, :posted_by, :member_id)'
        );
        $stmt->execute([
            'title'        => $title,
            'excerpt'      => $excerpt,
            'body'         => $body,
            'read_minutes' => $readMinutes,
            'published_at' => $published,
            'posted_by'    => $postedBy,
            'member_id'    => $memberId,
        ]);

        $id = (int) $this->db->lastInsertId();
        return $id ? $this->findById($id) : null;
    }

    private function hydrate(array $row): Article
    {
        return new Article(
            id:             (int) $row['id'],
            title:               $row['title'],
            excerpt:             $row['excerpt'],
            body:                $row['body'],
            readMinutes:   (int) $row['read_minutes'],
            publishedAt:         $row['published_at'],
            postedBy:            $row['posted_by']       ?? 'Agape House',
            memberId:            isset($row['member_id']) ? (int) $row['member_id'] : null,
            posterPicture:       $row['poster_picture']  ?? null,
            posterUsername:      $row['poster_username'] ?? null,
            commentCount:  (int) ($row['comment_count']  ?? 0),
        );
    }
}