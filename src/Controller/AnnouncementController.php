<?php
declare(strict_types=1);

namespace Controller;

use Service\AnnouncementService;

class AnnouncementController extends BaseController
{
    public function __construct(private readonly AnnouncementService $service) {}

    /** GET /api/announcements?category=Events */
    public function getAll(): void
    {
        $category = isset($_GET['category']) && $_GET['category'] !== ''
            ? $_GET['category']
            : null;

        $this->json([
            'status' => 'success',
            'data'   => $this->service->getAll($category),
            'total'  => $this->service->count(),
        ]);
    }

    /** GET /api/announcements/pinned */
    public function getPinned(): void
    {
        $this->json([
            'status' => 'success',
            'data'   => $this->service->getPinned(),
        ]);
    }

    /** GET /api/announcements/{id} */
    public function getOne(int $id): void
    {
        $a = $this->service->getById($id);
        if (!$a) {
            $this->error('Announcement not found.', 404);
            return;
        }
        $this->json(['status' => 'success', 'data' => $a]);
    }

    /** POST /api/announcements */
    public function create(): void
    {
        $data = $this->getJsonBody() ?? [];

        // Resolve poster name from session if not provided
        if (empty($data['posted_by'])) {
            $data['posted_by'] = $_SESSION['admin']['username']
                              ?? $_SESSION['member']['display_name']
                              ?? $_SESSION['member']['username']
                              ?? 'Agape House';
        }

        // Track which member posted this (null for admin posts)
        if (!isset($data['member_id']) && !empty($_SESSION['member']['id'])) {
            $data['member_id'] = (int) $_SESSION['member']['id'];
        }

        try {
            $a = $this->service->create($data);
            $this->json(['status' => 'success', 'data' => $a], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        }
    }

    /** PATCH /api/announcements/{id} */
    public function update(int $id): void
    {
        $data = $this->getJsonBody() ?? [];
        try {
            $a = $this->service->update($id, $data);
            if (!$a) {
                $this->error('Announcement not found.', 404);
                return;
            }
            $this->json(['status' => 'success', 'data' => $a]);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        }
    }

    /** DELETE /api/announcements/{id} */
    public function delete(int $id): void
    {
        $deleted = $this->service->delete($id);
        if (!$deleted) {
            $this->error('Announcement not found.', 404);
            return;
        }
        $this->json(['status' => 'success', 'message' => 'Deleted.']);
    }
}
