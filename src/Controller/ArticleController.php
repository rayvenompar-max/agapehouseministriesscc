<?php
/**
 * Controller\ArticleController
 *
 * GET  /api/articles            → getAll()
 * GET  /api/articles/{id}       → getOne()
 * POST /api/articles            → create()   (member or admin; member posts go pending)
 * GET  /api/articles/pending    → getPending()
 * POST /api/articles/{id}/approve → approve()
 * POST /api/articles/{id}/reject  → reject()
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

    /** Return articles awaiting approval (admin only). */
    public function getPending(): void
    {
        $this->success($this->service->getPending());
    }

    public function create(): void
    {
        // Must be logged in as a member or admin to post
        $isAdmin  = !empty($_SESSION['admin']['id']);
        $isMember = !empty($_SESSION['member']['id']);

        if (!$isAdmin && !$isMember) {
            $this->error('You must be signed in to submit an article.', 401);
        }

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

        // Admins post directly as approved; member submissions go into the pending queue
        $status = $isAdmin ? 'approved' : 'pending';

        $article = $this->service->create(
            $title, $excerpt, $body, $readMinutes, $publishedAt, $postedBy, $memberId, $status
        );

        $message = $isAdmin
            ? 'Article published successfully.'
            : 'Article submitted for review. It will appear once an admin approves it.';

        $this->success($article, $message, 201);
    }

    /** Approve a pending article (admin action). */
    public function approve(int $id): void
    {
        try {
            $result = $this->service->approve($id);
            $this->success($result, 'Article approved and published.');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 404);
        }
    }

    /** Reject a pending article (admin action). */
    public function reject(int $id): void
    {
        try {
            $result = $this->service->reject($id);
            $this->success($result, 'Article rejected.');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 404);
        }
    }
}
