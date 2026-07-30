<?php
declare(strict_types=1);

namespace Controller;

use Service\NotificationService;

class NotificationController extends BaseController
{
    public function __construct(private readonly NotificationService $service) {}

    /** GET /api/notifications — list recent notifications for the logged-in member */
    public function index(): void
    {
        $memberId = $this->requireMember();
        $notifications = $this->service->getForMember($memberId, 25);
        $unread        = $this->service->countUnread($memberId);
        $this->success(['notifications' => $notifications, 'unread' => $unread]);
    }

    /** GET /api/notifications/unread-count — lightweight poll endpoint */
    public function unreadCount(): void
    {
        $memberId = $this->requireMember();
        $this->success(['unread' => $this->service->countUnread($memberId)]);
    }

    /** POST /api/notifications/read-all — mark all as read */
    public function readAll(): void
    {
        $memberId = $this->requireMember();
        $this->service->markAllRead($memberId);
        $this->success(null, 'All notifications marked as read.');
    }

    /** POST /api/notifications/clear-all — delete all notifications */
    public function clearAll(): void
    {
        $memberId = $this->requireMember();
        $this->service->clearAll($memberId);
        $this->success(null, 'All notifications cleared.');
    }

    /** POST /api/notifications/{id}/read — mark one as read */
    public function readOne(int $id): void
    {
        $memberId = $this->requireMember();
        $this->service->markRead($id, $memberId);
        $this->success(null, 'Notification marked as read.');
    }

    /**
     * POST /api/notifications/like
     * Body: { target_type, target_id, target_title, recipient_id, liked }
     * liked=true  → create notification (only if recipient follows the actor)
     * liked=false → remove notification (un-like)
     */
    public function like(): void
    {
        $actorId = $this->requireMember();
        $body    = $this->getJsonBody() ?? [];

        $targetType  = trim($this->str($body, 'target_type'));
        $targetId    = $this->int($body, 'target_id');
        $targetTitle = trim($this->str($body, 'target_title'));
        $recipientId = $this->int($body, 'recipient_id');
        $liked       = (bool) ($body['liked'] ?? true);

        if ($targetId <= 0 || $recipientId <= 0) {
            $this->error('Invalid target_id or recipient_id.');
        }

        if ($liked) {
            // Only notify if the post owner (recipient) follows the actor
            if ($this->recipientFollowsActor($recipientId, $actorId)) {
                $this->service->notify(
                    $recipientId, $actorId, 'like',
                    $targetType, $targetId, $targetTitle
                );
            }
        } else {
            $this->service->removeLike($recipientId, $actorId, $targetType, $targetId);
        }

        $this->success(null);
    }

    /**
     * POST /api/notifications/share
     * Body: { target_type, target_id, target_title, recipient_id }
     * Only notifies if the recipient follows the actor.
     */
    public function share(): void
    {
        $actorId = $this->requireMember();
        $body    = $this->getJsonBody() ?? [];

        $targetType  = trim($this->str($body, 'target_type'));
        $targetId    = $this->int($body, 'target_id');
        $targetTitle = trim($this->str($body, 'target_title'));
        $recipientId = $this->int($body, 'recipient_id');

        if ($targetId <= 0 || $recipientId <= 0) {
            $this->error('Invalid target_id or recipient_id.');
        }

        // Only notify if the post owner (recipient) follows the actor
        if ($this->recipientFollowsActor($recipientId, $actorId)) {
            $this->service->notify(
                $recipientId, $actorId, 'share',
                $targetType, $targetId, $targetTitle
            );
        }

        $this->success(null);
    }

    /**
     * POST /api/notifications/comment
     * Body: { target_type, target_id, target_title, recipient_id }
     * Only notifies if the recipient follows the actor.
     */
    public function comment(): void
    {
        $actorId = $this->requireMember();
        $body    = $this->getJsonBody() ?? [];

        $targetType  = trim($this->str($body, 'target_type'));
        $targetId    = $this->int($body, 'target_id');
        $targetTitle = trim($this->str($body, 'target_title'));
        $recipientId = $this->int($body, 'recipient_id');

        if ($targetId <= 0 || $recipientId <= 0) {
            $this->error('Invalid target_id or recipient_id.');
        }

        // Only notify if the post owner (recipient) follows the actor
        if ($this->recipientFollowsActor($recipientId, $actorId)) {
            $this->service->notify(
                $recipientId, $actorId, 'comment',
                $targetType, $targetId, $targetTitle
            );
        }

        $this->success(null);
    }

    /**
     * POST /api/notifications/comment-like
     * Notify a comment author that their comment was liked (or un-liked).
     * Body: { comment_id, comment_body, recipient_id, target_type, target_id, target_title, liked }
     * Only notifies if the comment author (recipient) follows the actor.
     */
    public function commentLike(): void
    {
        $actorId = $this->requireMember();
        $body    = $this->getJsonBody() ?? [];

        $commentId   = $this->int($body, 'comment_id');
        $commentBody = mb_substr(trim($this->str($body, 'comment_body')), 0, 60);
        $recipientId = $this->int($body, 'recipient_id');
        $targetType  = trim($this->str($body, 'target_type'));
        $targetId    = $this->int($body, 'target_id');   // post ID
        $targetTitle = trim($this->str($body, 'target_title'));
        $liked       = (bool) ($body['liked'] ?? true);

        if ($commentId <= 0 || $recipientId <= 0 || $targetId <= 0) {
            $this->error('Invalid parameters.');
        }

        if ($liked) {
            // Only notify if the comment author (recipient) follows the actor
            if ($this->recipientFollowsActor($recipientId, $actorId)) {
                // target_id = post ID so clicking the notif opens the post modal
                $this->service->notify(
                    $recipientId, $actorId, 'comment_like',
                    $targetType, $targetId,
                    $commentBody ?: $targetTitle
                );
            }
        } else {
            $this->service->removeCommentLike($recipientId, $actorId, $commentId);
        }

        $this->success(null);
    }

    /**
     * POST /api/notifications/comment-reply
     * Notify a comment author that someone replied to their comment.
     * Body: { comment_id, comment_body, recipient_id, target_type, target_id, target_title }
     * Only notifies if the comment author (recipient) follows the actor.
     */
    public function commentReply(): void
    {
        $actorId = $this->requireMember();
        $body    = $this->getJsonBody() ?? [];

        $commentBody = mb_substr(trim($this->str($body, 'comment_body')), 0, 60);
        $recipientId = $this->int($body, 'recipient_id');
        $targetType  = trim($this->str($body, 'target_type'));
        $targetId    = $this->int($body, 'target_id');   // post ID
        $targetTitle = trim($this->str($body, 'target_title'));
        $commentId   = $this->int($body, 'comment_id');

        if ($recipientId <= 0 || $targetId <= 0) {
            $this->error('Invalid parameters.');
        }

        // Only notify if the comment author (recipient) follows the actor
        if ($this->recipientFollowsActor($recipientId, $actorId)) {
            $this->service->notify(
                $recipientId, $actorId, 'comment_reply',
                $targetType, $targetId,
                $commentBody ?: $targetTitle
            );
        }

        $this->success(null);
    }

    /**
     * POST /api/notifications/follow
     * Notify a member that someone followed them, or followed back.
     * The backend checks mutual following to determine the correct type.
     * Body: { recipient_id }
     */
    public function follow(): void
    {
        $actorId     = $this->requireMember();
        $body        = $this->getJsonBody() ?? [];
        $recipientId = $this->int($body, 'recipient_id');

        if ($recipientId <= 0) {
            $this->error('Invalid recipient_id.');
        }

        // Determine if this is a follow-back (recipient already follows the actor)
        $memberRepo = new \Repository\MemberRepository(\getDB());
        $isFollowBack = $memberRepo->isFollowing($recipientId, $actorId);
        $type = $isFollowBack ? 'follow_back' : 'follow';

        $this->service->notify(
            $recipientId, $actorId, $type,
            'member', $actorId, ''
        );
        $this->success(null);
    }

    /**
     * DELETE /api/notifications/follow
     * Remove a follow notification when someone unfollows (clean up).
     * Body: { recipient_id }
     */
    public function removeFollow(): void
    {
        $actorId = $this->requireMember();
        $body    = $this->getJsonBody() ?? [];

        $recipientId = $this->int($body, 'recipient_id');
        if ($recipientId <= 0) {
            $this->error('Invalid recipient_id.');
        }

        $this->service->removeFollow($recipientId, $actorId);
        $this->success(null);
    }

    /**
     * Returns true if $recipientId follows $actorId.
     * Used to gate interaction notifications — you only get notified
     * about actions from people you follow.
     */
    private function recipientFollowsActor(int $recipientId, int $actorId): bool
    {
        $memberRepo = new \Repository\MemberRepository(\getDB());
        return $memberRepo->isFollowing($recipientId, $actorId);
    }

    private function requireMember(): int
    {
        $member = $_SESSION['member'] ?? null;
        if (empty($member['id'])) {
            $this->error('Not logged in.', 401);
        }
        return (int) $member['id'];
    }
}
