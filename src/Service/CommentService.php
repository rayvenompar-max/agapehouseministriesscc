<?php
/**
 * Service\CommentService
 */
declare(strict_types=1);

namespace Service;

use Repository\CommentRepository;

class CommentService
{
    private const ALLOWED_TYPES = ['media', 'article', 'announcement', 'gallery'];
    private const MAX_BODY      = 1000;
    private const MIN_BODY      = 1;

    public function __construct(private readonly CommentRepository $repo) {}

    /**
     * Get top-level comments for a target, each with their replies nested.
     */
    public function getForTarget(string $targetType, int $targetId): array
    {
        $this->validateTargetType($targetType);

        $topLevel = array_map(fn($c) => $c->toArray(), $this->repo->findByTarget($targetType, $targetId));

        // Attach replies to each top-level comment
        foreach ($topLevel as &$comment) {
            $replies = $this->repo->findReplies($comment['id']);
            $comment['replies'] = array_map(fn($r) => $r->toArray(), $replies);
        }
        unset($comment);

        return $topLevel;
    }

    /** Post a new comment or reply. */
    public function create(
        int    $memberId,
        string $targetType,
        int    $targetId,
        string $body,
        ?int   $parentId = null
    ): array {
        $this->validateTargetType($targetType);

        $body = trim($body);
        if (strlen($body) < self::MIN_BODY) {
            throw new \InvalidArgumentException('Comment cannot be empty.');
        }
        if (strlen($body) > self::MAX_BODY) {
            throw new \InvalidArgumentException('Comment must be 1000 characters or fewer.');
        }

        $comment = $this->repo->create($memberId, $targetType, $targetId, $body, $parentId);
        $arr     = $comment->toArray();
        $arr['replies'] = [];
        return $arr;
    }

    /** Toggle like on a comment. liked=true increments, liked=false decrements. */
    public function toggleLike(int $commentId, bool $liked): int
    {
        return $liked
            ? $this->repo->incrementLike($commentId)
            : $this->repo->decrementLike($commentId);
    }

    /** Delete a comment (author only). Also removes replies. */
    public function delete(int $commentId, int $memberId): bool
    {
        $deleted = $this->repo->delete($commentId, $memberId);
        if (!$deleted) {
            throw new \InvalidArgumentException('Comment not found or you are not the author.');
        }
        return true;
    }

    private function validateTargetType(string $type): void
    {
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException('Invalid target type.');
        }
    }
}
