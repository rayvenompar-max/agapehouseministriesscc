<?php
/**
 * Controller\CommentController
 *
 * GET    /api/comments/{type}/{id}      → getForTarget()
 * POST   /api/comments                  → create()      (body + optional parent_id)
 * POST   /api/comments/{id}/like        → like()        (body: { liked: bool })
 * DELETE /api/comments/{id}             → delete()
 */
declare(strict_types=1);

namespace Controller;

use Service\CommentService;

class CommentController extends BaseController
{
    public function __construct(private readonly CommentService $service) {}

    /** GET /api/comments/{type}/{id} */
    public function getForTarget(string $type, int $id): void
    {
        try {
            $comments = $this->service->getForTarget($type, $id);
            $this->success($comments);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    /** POST /api/comments — requires member session */
    public function create(): void
    {
        $memberId = $this->requireMember();

        $body = $this->getJsonBody();
        if (!$body) {
            $this->error('Request body is required.');
        }

        $targetType = trim($this->str($body, 'target_type'));
        $targetId   = $this->int($body, 'target_id');
        $text       = trim($this->str($body, 'body'));
        $parentId   = isset($body['parent_id']) && $body['parent_id'] !== null
                        ? (int) $body['parent_id'] : null;

        if ($targetId <= 0) {
            $this->error('Invalid target_id.');
        }

        try {
            $comment = $this->service->create($memberId, $targetType, $targetId, $text, $parentId);
            $this->success($comment, 'Comment posted.', 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    /** POST /api/comments/{id}/like — body: { liked: true|false } */
    public function like(int $id): void
    {
        $this->requireMember();

        $body  = $this->getJsonBody() ?? [];
        $liked = (bool) ($body['liked'] ?? true);

        $newCount = $this->service->toggleLike($id, $liked);
        $this->success(['like_count' => $newCount]);
    }

    /** DELETE /api/comments/{id} — author only */
    public function delete(int $id): void
    {
        $memberId = $this->requireMember();

        try {
            $this->service->delete($id, $memberId);
            $this->success(null, 'Comment deleted.');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 403);
        }
    }

    private function requireMember(): int
    {
        $member = $_SESSION['member'] ?? null;
        if (empty($member['id'])) {
            $this->error('You must be signed in to comment.', 401);
        }
        return (int) $member['id'];
    }
}
