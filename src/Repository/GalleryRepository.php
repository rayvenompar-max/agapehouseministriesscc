<?php
declare(strict_types=1);

namespace Repository;

use PDO;

class GalleryRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all approved gallery items with their images
     */
    public function getApproved(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            'SELECT g.*, m.username, m.display_name, m.profile_picture
             FROM gallery g
             INNER JOIN members m ON g.member_id = m.id
             WHERE g.status = :status
             ORDER BY g.created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':status', 'approved', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch images for each gallery item
        foreach ($items as &$item) {
            $item['images'] = $this->getImages((int) $item['id']);
        }
        
        return $items;
    }

    /**
     * Get all pending gallery items (for admin approval) with their images
     */
    public function getPending(): array
    {
        $stmt = $this->db->prepare(
            'SELECT g.*, m.username, m.display_name, m.profile_picture
             FROM gallery g
             INNER JOIN members m ON g.member_id = m.id
             WHERE g.status = :status
             ORDER BY g.created_at ASC'
        );
        $stmt->bindValue(':status', 'pending', PDO::PARAM_STR);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch images for each gallery item
        foreach ($items as &$item) {
            $item['images'] = $this->getImages((int) $item['id']);
        }
        
        return $items;
    }

    /**
     * Get gallery item by ID with its images
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT g.*, m.username, m.display_name, m.profile_picture
             FROM gallery g
             INNER JOIN members m ON g.member_id = m.id
             WHERE g.id = :id
             LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $result['images'] = $this->getImages($id);
        }
        
        return $result ?: null;
    }

    /**
     * Get images for a gallery item
     */
    public function getImages(int $galleryId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, image_url, display_order
             FROM gallery_images
             WHERE gallery_id = :gallery_id
             ORDER BY display_order ASC'
        );
        $stmt->bindValue(':gallery_id', $galleryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new gallery submission with multiple images
     */
    public function create(int $memberId, string $title, string $description, array $imageUrls): int
    {
        // Create the gallery entry with the first image as primary
        $primaryImage = $imageUrls[0] ?? '';
        
        $stmt = $this->db->prepare(
            'INSERT INTO gallery (member_id, title, description, image_url, status)
             VALUES (:member_id, :title, :description, :image_url, :status)'
        );
        $stmt->execute([
            ':member_id' => $memberId,
            ':title' => $title,
            ':description' => $description,
            ':image_url' => $primaryImage,
            ':status' => 'pending'
        ]);
        
        $galleryId = (int) $this->db->lastInsertId();
        
        // Insert all images into gallery_images table
        if (!empty($imageUrls)) {
            $this->addImages($galleryId, $imageUrls);
        }
        
        return $galleryId;
    }

    /**
     * Add images to a gallery item
     */
    public function addImages(int $galleryId, array $imageUrls): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO gallery_images (gallery_id, image_url, display_order)
             VALUES (:gallery_id, :image_url, :display_order)'
        );
        
        foreach ($imageUrls as $index => $imageUrl) {
            $stmt->execute([
                ':gallery_id' => $galleryId,
                ':image_url' => $imageUrl,
                ':display_order' => $index
            ]);
        }
    }

    /**
     * Update a gallery item's title and description
     */
    public function update(int $id, string $title, string $description): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE gallery SET title = :title, description = :description 
             WHERE id = :id'
        );
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':id' => $id
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Approve a gallery item
     */
    public function approve(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE gallery SET status = :status WHERE id = :id'
        );
        $stmt->execute([
            ':status' => 'approved',
            ':id' => $id
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Reject a gallery item
     */
    public function reject(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE gallery SET status = :status WHERE id = :id'
        );
        $stmt->execute([
            ':status' => 'rejected',
            ':id' => $id
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a gallery item
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM gallery WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get gallery items by member with their images
     */
    public function getByMember(int $memberId, ?string $status = null): array
    {
        $sql = 'SELECT g.*, m.username, m.display_name, m.profile_picture
                FROM gallery g
                INNER JOIN members m ON g.member_id = m.id
                WHERE g.member_id = :member_id';
        
        if ($status !== null) {
            $sql .= ' AND g.status = :status';
        }
        
        $sql .= ' ORDER BY g.created_at DESC';
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':member_id', $memberId, PDO::PARAM_INT);
        
        if ($status !== null) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch images for each gallery item
        foreach ($items as &$item) {
            $item['images'] = $this->getImages((int) $item['id']);
        }
        
        return $items;
    }

    /**
     * Count gallery items by status
     */
    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM gallery WHERE status = :status');
        $stmt->execute([':status' => $status]);
        return (int) $stmt->fetchColumn();
    }
}
