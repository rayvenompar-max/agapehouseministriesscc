<?php
/**
 * Controller\ContactController
 *
 * POST   /api/contact                   → send()        — member/guest
 * GET    /api/contact                   → list()        — admin only
 * POST   /api/contact/{id}/read         → markRead()    — admin only
 * POST   /api/contact/{id}/reply        → adminReply()  — admin only
 * GET    /api/contact/{id}/thread       → getThread()   — admin OR owner member
 * POST   /api/contact/{id}/message      → memberReply() — owner member only
 */
declare(strict_types=1);

namespace Controller;

use Service\ContactService;

class ContactController extends BaseController
{
    public function __construct(private readonly ContactService $service) {}

    /** POST /api/contact — submit the connect form */
    public function send(): void
    {
        $body = $this->getJsonBody();

        if (!$body) {
            $this->error('Request body is required.');
        }

        // Attach the logged-in member id if present
        $memberId = !empty($_SESSION['member']['id'])
            ? (int) $_SESSION['member']['id']
            : null;

        try {
            $result = $this->service->send(
                name:     $this->str($body, 'name'),
                email:    $this->str($body, 'email'),
                reason:   $this->str($body, 'reason'),
                message:  $this->str($body, 'message'),
                memberId: $memberId,
            );
            $this->success($result, $result['message'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    /** GET /api/contact — admin: list all messages */
    public function list(): void
    {
        if (empty($_SESSION['admin'])) {
            $this->error('Unauthorised.', 403);
        }

        $messages = $this->service->getAll();
        $this->success($messages);
    }

    /** POST /api/contact/{id}/read — admin: mark as read */
    public function markRead(int $id): void
    {
        if (empty($_SESSION['admin'])) {
            $this->error('Unauthorised.', 403);
        }

        $this->service->markRead($id);
        $this->success(null, 'Marked as read.');
    }

    /** POST /api/contact/{id}/reply — admin sends a reply */
    public function adminReply(int $id): void
    {
        if (empty($_SESSION['admin'])) {
            $this->error('Unauthorised.', 403);
        }

        $body = $this->getJsonBody() ?? [];
        $text = trim($this->str($body, 'body'));

        try {
            $msg = $this->service->adminReply($id, $text);
            $this->success($msg, 'Reply sent.', 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * GET /api/contact/{id}/thread
     * Admin: any thread. Member: only their own thread.
     */
    public function getThread(int $id): void
    {
        $isAdmin  = !empty($_SESSION['admin']);
        $memberId = !empty($_SESSION['member']['id']) ? (int) $_SESSION['member']['id'] : 0;

        if (!$isAdmin && !$memberId) {
            $this->error('Unauthorised.', 401);
        }

        $thread = $this->service->getThread($id);

        if (!$thread) {
            $this->error('Not found.', 404);
        }

        // Members can only see their own thread
        if (!$isAdmin) {
            if ((int) ($thread['contact']['member_id'] ?? 0) !== $memberId) {
                $this->error('Unauthorised.', 403);
            }
        }

        $this->success($thread);
    }

    /** POST /api/contact/{id}/message — member sends a follow-up */
    public function memberReply(int $id): void
    {
        if (empty($_SESSION['member']['id'])) {
            $this->error('Not logged in.', 401);
        }

        $memberId = (int) $_SESSION['member']['id'];
        $body     = $this->getJsonBody() ?? [];
        $text     = trim($this->str($body, 'body'));

        try {
            $msg = $this->service->memberReply($id, $memberId, $text);
            $this->success($msg, 'Message sent.', 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    /** GET /api/contact/threads — member: get all their contact threads */
    public function getThreads(): void
    {
        if (empty($_SESSION['member']['id'])) {
            $this->error('Not logged in.', 401);
        }

        $memberId = (int) $_SESSION['member']['id'];
        $threads  = $this->service->getMemberThreads($memberId);
        $this->success($threads);
    }
}
