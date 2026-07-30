<?php
declare(strict_types=1);

namespace Service;

use Repository\AnnouncementRepository;
use Repository\NotificationRepository;
use Repository\MemberRepository;

class AnnouncementService
{
    public function __construct(
        private readonly AnnouncementRepository $repo,
        private readonly NotificationRepository $notificationRepo,
        private readonly MemberRepository $memberRepo
    ) {}

    public function getAll(?string $category = null): array
    {
        $items = $this->repo->findAll($category);
        return array_map(fn($a) => $a->toArray(), $items);
    }

    public function getPinned(): ?array
    {
        $a = $this->repo->findPinned();
        return $a ? $a->toArray() : null;
    }

    public function getById(int $id): ?array
    {
        $a = $this->repo->findById($id);
        return $a ? $a->toArray() : null;
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function create(array $data): array
    {
        $this->validate($data);
        $announcement = $this->repo->create($data);
        
        // Notify all active members about the new announcement
        if ($announcement) {
            $this->notifyAllMembers($announcement->id, $announcement->title);
        }
        
        return $announcement->toArray();
    }

    /**
     * Notify all active members about a new announcement.
     * Uses actor_id = 0 (system/broadcast) so the notification is never skipped.
     */
    private function notifyAllMembers(int $announcementId, string $announcementTitle): void
    {
        $memberIds = $this->memberRepo->getAllActiveMemberIds();

        foreach ($memberIds as $memberId) {
            $this->notificationRepo->createBroadcast(
                recipientId:  $memberId,
                type:         'new_announcement',
                targetType:   'announcement',
                targetId:     $announcementId,
                targetTitle:  $announcementTitle
            );
        }
    }

    public function update(int $id, array $data): ?array
    {
        if (!empty($data['title'])) {
            $this->validateTitle($data['title']);
        }
        $a = $this->repo->update($id, $data);
        return $a ? $a->toArray() : null;
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($id);
    }

    // ── Validation ─────────────────────────────────────────────────────────────

    private function validate(array $data): void
    {
        if (empty($data['title']) || trim($data['title']) === '') {
            throw new \InvalidArgumentException('Title is required.');
        }
        $this->validateTitle($data['title']);

        if (empty($data['body']) || trim($data['body']) === '') {
            throw new \InvalidArgumentException('Body is required.');
        }

        $allowed = ['Ministry', 'Events', 'Community', 'Urgent'];
        if (isset($data['category']) && !in_array($data['category'], $allowed, true)) {
            throw new \InvalidArgumentException('Invalid category.');
        }
    }

    private function validateTitle(string $title): void
    {
        if (mb_strlen(trim($title)) > 255) {
            throw new \InvalidArgumentException('Title must be 255 characters or fewer.');
        }
    }
}
