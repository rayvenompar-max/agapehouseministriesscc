<?php
/**
 * Service\PrayerService
 *
 * Business logic for the Prayer Wall.
 */
declare(strict_types=1);

namespace Service;

use Repository\PrayerRepository;

class PrayerService
{
    private const ALLOWED_CATEGORIES = [
        'Healing', 'Family', 'Guidance', 'Provision', 'Thanksgiving',
    ];

    public function __construct(private readonly PrayerRepository $repo) {}

    public function getWall(): array
    {
        return array_map(fn($p) => $p->toArray(), $this->repo->findApproved());
    }

    public function getPending(): array
    {
        return array_map(fn($p) => $p->toArray(), $this->repo->findPending());
    }

    /**
     * Submit a new prayer request.
     * Returns ['success' => true, 'message' => ...] or throws on validation failure.
     */
    public function submit(string $name, string $category, string $body): array
    {
        // Validate
        $body = trim($body);
        if (strlen($body) < 10) {
            throw new \InvalidArgumentException('Prayer request is too short.');
        }
        if (strlen($body) > 1000) {
            throw new \InvalidArgumentException('Prayer request must be 1000 characters or fewer.');
        }
        if (!in_array($category, self::ALLOWED_CATEGORIES, true)) {
            throw new \InvalidArgumentException('Invalid category.');
        }

        $name = trim($name);

        $request = $this->repo->create($name, $category, $body);

        return [
            'success' => true,
            'message' => 'Your request has been posted to the wall.',
            'id'      => $request->id,
        ];
    }

    /**
     * Record that a visitor prayed for a request.
     */
    public function pray(int $id): array
    {
        $exists = $this->repo->findById($id);
        if (!$exists) {
            throw new \InvalidArgumentException("Prayer request #{$id} not found.");
        }

        $this->repo->incrementPrayCount($id);
        $updated = $this->repo->findById($id);

        return [
            'success'    => true,
            'pray_count' => $updated->prayCount,
        ];
    }

    /**
     * Approve a prayer request.
     */
    public function approve(int $id): array
    {
        $exists = $this->repo->findById($id);
        if (!$exists) {
            throw new \InvalidArgumentException("Prayer request #{$id} not found.");
        }

        $this->repo->updateStatus($id, 'approved');

        return ['success' => true];
    }

    /**
     * Reject a prayer request.
     */
    public function reject(int $id): array
    {
        $exists = $this->repo->findById($id);
        if (!$exists) {
            throw new \InvalidArgumentException("Prayer request #{$id} not found.");
        }

        $this->repo->updateStatus($id, 'rejected');

        return ['success' => true];
    }
}
