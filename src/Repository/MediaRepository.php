<?php
/**
 * Repository\MediaRepository
 *
 * Handles all DB reads/writes for the Media model.
 */
declare(strict_types=1);

namespace Repository;

use Model\Media;
use PDO;

class MediaRepository
{
    public function __construct(private readonly PDO $db) {}

    /** Return all media, optionally filtered by type. */
    public function findAll(?string $type = null): array
    {
        if ($type) {
            $stmt = $this->db->prepare(
                'SELECT m.*, mem.profile_picture AS poster_picture, mem.username AS poster_username,
                        (SELECT COUNT(*) FROM comments c WHERE c.target_type = \'media\' AND c.target_id = m.id) AS comment_count
                 FROM media m
                 LEFT JOIN members mem ON mem.display_name = m.posted_by
                 WHERE m.type = :type ORDER BY m.published_at DESC'
            );
            $stmt->execute(['type' => $type]);
        } else {
            $stmt = $this->db->query(
                'SELECT m.*, mem.profile_picture AS poster_picture, mem.username AS poster_username,
                        (SELECT COUNT(*) FROM comments c WHERE c.target_type = \'media\' AND c.target_id = m.id) AS comment_count
                 FROM media m
                 LEFT JOIN members mem ON mem.display_name = m.posted_by
                 ORDER BY m.published_at DESC'
            );
        }

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /** Return the single featured media item. */
    public function findFeatured(): ?Media
    {
        $stmt = $this->db->query(
            'SELECT m.*, mem.profile_picture AS poster_picture, mem.username AS poster_username
             FROM media m
             LEFT JOIN members mem ON mem.display_name = m.posted_by
             WHERE m.featured = 1 ORDER BY m.published_at DESC LIMIT 1'
        );
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function findById(int $id): ?Media
    {
        $stmt = $this->db->prepare(
            'SELECT m.*, mem.profile_picture AS poster_picture, mem.username AS poster_username
             FROM media m
             LEFT JOIN members mem ON mem.display_name = m.posted_by
             WHERE m.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Insert a new media row and return the newly created Media object.
     */
    public function create(array $fields): Media
    {
        $stmt = $this->db->prepare(
            'INSERT INTO media (title, description, type, series, posted_by, member_id, duration, thumbnail, video_url, featured, published_at)
             VALUES (:title, :description, :type, :series, :posted_by, :member_id, :duration, :thumbnail, :video_url, :featured, :published_at)'
        );
        $stmt->execute([
            'title'        => $fields['title'],
            'description'  => $fields['description'],
            'type'         => $fields['type'],
            'series'       => $fields['series']       ?? '',
            'posted_by'    => $fields['posted_by']    ?? 'Agape House',
            'member_id'    => $fields['member_id']    ?? null,
            'duration'     => $fields['duration']     ?? 0,
            'thumbnail'    => $fields['thumbnail']    ?? '',
            'video_url'    => $fields['video_url']    ?? '',
            'featured'     => $fields['featured']     ?? 0,
            'published_at' => $fields['published_at'] ?? date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->lastInsertId();
        return $this->findById($id);
    }

    /**
     * Delete a media row by ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM media WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update arbitrary allowed fields on a media row.
     * $fields must be a pre-validated key→value map.
     */
    public function updateFields(int $id, array $fields): bool
    {
        $setClauses = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($fields)));
        $stmt = $this->db->prepare("UPDATE media SET {$setClauses} WHERE id = :id");
        $fields['id'] = $id;
        $stmt->execute($fields);
        return $stmt->rowCount() > 0;
    }

    private function hydrate(array $row): Media
    {
        return new Media(
            id:            (int)  $row['id'],
            title:                $row['title'],
            description:          $row['description'],
            type:                 $row['type'],
            series:               $row['series']          ?? '',
            postedBy:             $row['posted_by']       ?? 'Agape House',
            memberId:             isset($row['member_id']) ? (int) $row['member_id'] : null,
            posterPicture:        $row['poster_picture']  ?? null,
            posterUsername:       $row['poster_username'] ?? null,
            duration:      (int)  $row['duration'],
            thumbnail:            $row['thumbnail']       ?? '',
            videoUrl:             $row['video_url']       ?? '',
            featured:      (bool) $row['featured'],
            publishedAt:          $row['published_at'],
            commentCount:  (int) ($row['comment_count']  ?? 0),
        );
    }
}
