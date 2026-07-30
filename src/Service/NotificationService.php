<?php
declare(strict_types=1);

namespace Service;

use Repository\NotificationRepository;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $repo
    ) {}

    public function notify(
        int    $recipientId,
        int    $actorId,
        string $type,
        string $targetType,
        int    $targetId,
        string $targetTitle
    ): void {
        $this->repo->create($recipientId, $actorId, $type, $targetType, $targetId, $targetTitle);
    }

    public function getForMember(int $memberId, int $limit = 25): array
    {
        return $this->repo->findForMember($memberId, $limit);
    }

    public function countUnread(int $memberId): int
    {
        return $this->repo->countUnread($memberId);
    }

    public function markAllRead(int $memberId): void
    {
        $this->repo->markAllRead($memberId);
    }

    public function clearAll(int $memberId): void
    {
        $this->repo->clearAll($memberId);
    }

    public function markRead(int $notificationId, int $memberId): void
    {
        $this->repo->markRead($notificationId, $memberId);
    }

    public function removeLike(
        int    $recipientId,
        int    $actorId,
        string $targetType,
        int    $targetId
    ): void {
        $this->repo->deleteLike($recipientId, $actorId, $targetType, $targetId);
    }

    public function removeCommentLike(int $recipientId, int $actorId, int $commentId): void
    {
        $this->repo->deleteCommentLike($recipientId, $actorId, $commentId);
    }

    public function removeFollow(int $recipientId, int $actorId): void
    {
        $this->repo->deleteFollow($recipientId, $actorId);
    }
}
