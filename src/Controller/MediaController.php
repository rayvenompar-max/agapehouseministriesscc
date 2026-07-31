<?php
/**
 * Controller\MediaController
 *
 * Handles API requests for the Watch & Listen section.
 *
 * GET    /api/media            → getListing()   (approved only)
 * GET    /api/media/featured   → getFeatured()  (approved only)
 * GET    /api/media/pending    → getPending()   (admin)
 * POST   /api/media            → create()       (member/admin; member goes pending)
 * PATCH  /api/media/{id}       → update()
 * DELETE /api/media/{id}       → delete()
 * POST   /api/media/{id}/approve → approve()   (admin)
 * POST   /api/media/{id}/reject  → reject()    (admin)
 */
declare(strict_types=1);

namespace Controller;

use Service\MediaService;

class MediaController extends BaseController
{
    public function __construct(private readonly MediaService $service) {}

    public function getListing(): void
    {
        $type = isset($_GET['type']) ? trim($_GET['type']) : null;

        try {
            $items = $this->service->getListing($type ?: null);
            $this->success($items);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    public function getFeatured(): void
    {
        $featured = $this->service->getFeatured();

        if (!$featured) {
            $this->success(null, 'No featured media.');
            return;
        }

        $this->success($featured);
    }

    public function getOne(int $id): void
    {
        $item = $this->service->findById($id);
        if (!$item) {
            $this->error("Media #{$id} not found.", 404);
        }
        $this->success($item);
    }

    /** Return media items pending admin approval. */
    public function getPending(): void
    {
        $this->success($this->service->getPending());
    }

    public function create(): void
    {
        $isAdmin  = !empty($_SESSION['admin']['id']);
        $isMember = !empty($_SESSION['member']['id']);

        if (!$isAdmin && !$isMember) {
            $this->error('You must be signed in to upload media.', 401);
        }

        $body = $this->getJsonBody();
        if (!$body) {
            $this->error('Request body is required.');
        }

        // Inject poster name from session if not provided in the request
        if (empty($body['posted_by'])) {
            $body['posted_by'] = $_SESSION['admin']['username']
                              ?? $_SESSION['member']['display_name']
                              ?? $_SESSION['member']['username']
                              ?? 'Agape House';
        }

        // Inject the uploading member's ID so ownership can be tracked
        if (empty($body['member_id']) && !empty($_SESSION['member']['id'])) {
            $body['member_id'] = (int) $_SESSION['member']['id'];
        }

        // Admins post directly as approved; member submissions require approval
        $body['status'] = $isAdmin ? 'approved' : 'pending';

        try {
            $result = $this->service->create($body);
            $message = $isAdmin
                ? 'Media created.'
                : 'Video submitted for review. It will appear once an admin approves it.';
            $this->success($result, $message, 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    public function update(int $id): void
    {
        $body = $this->getJsonBody();
        if (!$body) {
            $this->error('Request body is required.');
        }

        // Ownership check: members can only edit their own uploads
        // Admins (session key 'admin') bypass this check
        if (empty($_SESSION['admin']['id'])) {
            $currentMemberId = $_SESSION['member']['id'] ?? null;
            if (!$currentMemberId) {
                $this->error('You must be signed in to edit media.', 401);
            }
            $existing = $this->service->findById($id);
            if (!$existing) {
                $this->error('Media not found.', 404);
            }
            if ($existing['member_id'] !== (int) $currentMemberId) {
                $this->error('You can only edit videos you uploaded.', 403);
            }
        }

        try {
            $result = $this->service->update($id, $body);
            $this->success($result, 'Media updated.');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 404);
        }
    }

    public function delete(int $id): void
    {
        // Ownership check: members can only delete their own uploads
        // Admins bypass this check
        if (empty($_SESSION['admin']['id'])) {
            $currentMemberId = $_SESSION['member']['id'] ?? null;
            if (!$currentMemberId) {
                $this->error('You must be signed in to delete media.', 401);
            }
            $existing = $this->service->findById($id);
            if (!$existing) {
                $this->error('Media not found.', 404);
            }
            if ($existing['member_id'] !== (int) $currentMemberId) {
                $this->error('You can only delete videos you uploaded.', 403);
            }
        }

        try {
            $deleted = $this->service->delete($id);
            if ($deleted) {
                $this->success(null, 'Media deleted.');
            } else {
                $this->error('Media not found.', 404);
            }
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 404);
        }
    }

    /** Approve a pending media item (admin action). */
    public function approve(int $id): void
    {
        try {
            $result = $this->service->approve($id);
            $this->success($result, 'Media approved and published.');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 404);
        }
    }

    /** Reject a pending media item (admin action). */
    public function reject(int $id): void
    {
        try {
            $result = $this->service->reject($id);
            $this->success($result, 'Media rejected.');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 404);
        }
    }
}
