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

    public function create(
        string $title,
        string $excerpt,
        string $body,
        int    $readMinutes,
        ?string $publishedAt,
        string  $postedBy = 'Agape House',
        ?int    $memberId = null,
    ): array {
        return $this->repo->insert($title, $excerpt, $body, $readMinutes, $publishedAt, $postedBy, $memberId)?->toArray() ?? [];
    }
}
