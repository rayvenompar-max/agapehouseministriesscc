<?php
/**
 * Service\ArticleService
 *
 * Business logic for the Read / devotionals section.
 */
declare(strict_types=1);

namespace Service;

use Repository\ArticleRepository;

class ArticleService
{
    public function __construct(private readonly ArticleRepository $repo) {}

    public function getAll(): array
    {
        return array_map(fn($a) => $a->toArray(), $this->repo->findAll());
    }

    public function getOne(int $id): ?array
    {
        return $this->repo->findById($id)?->toArray();
    }

    /** Return articles pending admin approval. */
    public function getPending(): array
    {
        return array_map(fn($a) => $a->toArray(), $this->repo->findPending());
    }

    /**
     * @param string $status  'pending' for member submissions, 'approved' for admin posts
     */
    public function create(
        string  $title,
        string  $excerpt,
        string  $body,
        int     $readMinutes,
        ?string $publishedAt,
        string  $postedBy = 'Agape House',
        ?int    $memberId = null,
        string  $status   = 'pending',
    ): array {
        return $this->repo->insert(
            $title, $excerpt, $body, $readMinutes, $publishedAt, $postedBy, $memberId, $status
        )?->toArray() ?? [];
    }

    /** Approve an article and make it publicly visible. */
    public function approve(int $id): array
    {
        $exists = $this->repo->findById($id);
        if (!$exists) {
            throw new \InvalidArgumentException("Article #{$id} not found.");
        }
        $this->repo->updateStatus($id, 'approved');
        return ['success' => true];
    }

    /** Reject an article so it won't appear on the site. */
    public function reject(int $id): array
    {
        $exists = $this->repo->findById($id);
        if (!$exists) {
            throw new \InvalidArgumentException("Article #{$id} not found.");
        }
        $this->repo->updateStatus($id, 'rejected');
        return ['success' => true];
    }
}
