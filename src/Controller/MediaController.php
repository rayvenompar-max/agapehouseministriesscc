<?php
/**
 * Controller\MediaController
 *
 * Handles API requests for the Watch & Listen section.
 *
 * GET  /api/media          → getListing()
 * GET  /api/media/featured → getFeatured()
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
            // Return 200 with null data — "no featured" is a normal empty state,
            // not a real error. A 404 causes noisy browser console warnings.
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

    public function create(): void
    {
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

        try {
            $result = $this->service->create($body);
            $this->success($result, 'Media created.', 201);
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
}
