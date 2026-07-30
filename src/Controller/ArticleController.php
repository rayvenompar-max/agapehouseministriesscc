<?php
/**
 * Controller\ArticleController
 *
 * GET /api/articles        → getAll()
 * GET /api/articles/{id}   → getOne()
 */
declare(strict_types=1);

namespace Controller;

use Service\ArticleService;

class ArticleController extends BaseController
{
    public function __construct(private readonly ArticleService $service) {}

    public function getAll(): void
    {
        $this->success($this->service->getAll());
    }

    public function getOne(int $id): void
    {
        $article = $this->service->getOne($id);

        if (!$article) {
            $this->error("Article #{$id} not found.", 404);
        }

        $this->success($article);
    }

    public function create(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $title        = trim($data['title']        ?? '');
        $excerpt      = trim($data['excerpt']      ?? '');
        $body         = trim($data['body']         ?? '');
        $readMinutes  = max(1, (int) ($data['read_minutes'] ?? 5));
        $publishedAt  = !empty($data['published_at']) ? $data['published_at'] : null;

        // Resolve poster: prefer explicit field, then admin session, then member session
        $postedBy = trim($data['posted_by'] ?? '');
        if ($postedBy === '') {
            $postedBy = $_SESSION['admin']['username']
                     ?? $_SESSION['member']['display_name']
                     ?? $_SESSION['member']['username']
                     ?? 'Agape House';
        }

        // Track which member posted this (so their avatar can be shown)
        $memberId = isset($_SESSION['member']['id']) ? (int) $_SESSION['member']['id'] : null;

        if ($title === '')   { $this->error('Title is required.',         422); return; }
        if ($excerpt === '') { $this->error('Excerpt is required.',       422); return; }
        if ($body === '')    { $this->error('Article body is required.',  422); return; }

        $article = $this->service->create($title, $excerpt, $body, $readMinutes, $publishedAt, $postedBy, $memberId);
        $this->success($article, 'Article published successfully.', 201);
    }
}
