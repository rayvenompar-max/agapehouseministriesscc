<?php
/**
 * Controller\PrayerController
 *
 * GET  /api/prayers            → getWall()
 * POST /api/prayers            → submit()
 * POST /api/prayers/{id}/pray  → pray()
 */
declare(strict_types=1);

namespace Controller;

use Service\PrayerService;

class PrayerController extends BaseController
{
    public function __construct(private readonly PrayerService $service) {}

    public function getWall(): void
    {
        $this->success($this->service->getWall());
    }

    public function getPending(): void
    {
        $this->success($this->service->getPending());
    }

    public function submit(): void
    {
        $body = $this->getJsonBody();

        if (!$body) {
            $this->error('Request body is required.');
        }

        // Always use the logged-in member's display name
        $member = $_SESSION['member'] ?? null;
        $name   = ($member && !empty($member['display_name']))
                    ? $member['display_name']
                    : 'Anonymous';

        try {
            $result = $this->service->submit(
                name:     $name,
                category: $this->str($body, 'category'),
                body:     $this->str($body, 'body'),
            );
            $this->success($result, $result['message'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    public function pray(int $id): void
    {
        try {
            $result = $this->service->pray($id);
            $this->success($result, 'Your prayer was recorded.');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 404);
        }
    }

    public function approve(int $id): void
    {
        try {
            $result = $this->service->approve($id);
            $this->success($result, 'Prayer request approved.');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 404);
        }
    }

    public function reject(int $id): void
    {
        try {
            $result = $this->service->reject($id);
            $this->success($result, 'Prayer request rejected.');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 404);
        }
    }
}
