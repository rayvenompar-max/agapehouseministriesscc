<?php
/**
 * Service\MediaService
 *
 * Business logic for the Watch & Listen section.
 */
declare(strict_types=1);

namespace Service;

use Repository\MediaRepository;

class MediaService
{
    public function __construct(private readonly MediaRepository $repo) {}

    /** Return a single media item by ID, or null. */
    public function findById(int $id): ?array
    {
        $item = $this->repo->findById($id);
        return $item?->toArray();
    }

    /** Return the featured media or null. */
    public function getFeatured(): ?array
    {
        $media = $this->repo->findFeatured();
        return $media?->toArray();
    }

    /**
     * Return a filtered list of media.
     * $type: null = all, 'sermon', 'devotional', 'testimony', 'worship'
     */
    public function getListing(?string $type = null): array
    {
        $allowed = ['sermon', 'devotional', 'testimony', 'worship'];

        if ($type !== null && !in_array($type, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid media type: {$type}");
        }

        return array_map(
            fn($m) => $m->toArray(),
            $this->repo->findAll($type)
        );
    }

    /**
     * Create a new media item.
     * Member submissions default to 'pending'; admin posts are 'approved' immediately.
     */
    public function create(array $data): array
    {
        $allowed = ['sermon', 'devotional', 'testimony', 'worship'];

        $title = trim((string) ($data['title'] ?? ''));
        $type  = trim((string) ($data['type']  ?? ''));

        if ($title === '') {
            throw new \InvalidArgumentException('Title is required.');
        }
        if (!in_array($type, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid media type: {$type}");
        }

        $fields = [
            'title'        => $title,
            'description'  => trim((string) ($data['description']  ?? '')),
            'type'         => $type,
            'series'       => trim((string) ($data['series']        ?? '')),
            'posted_by'    => trim((string) ($data['posted_by']     ?? 'Agape House')),
            'member_id'    => isset($data['member_id']) ? (int) $data['member_id'] : null,
            'duration'     => max(0, (int) ($data['duration']       ?? 0)),
            'thumbnail'    => trim((string) ($data['thumbnail']     ?? '')),
            'video_url'    => trim((string) ($data['video_url']     ?? '')),
            'featured'     => (int) (bool) ($data['featured']       ?? false),
            'published_at' => isset($data['published_at']) && $data['published_at'] !== ''
                                ? $data['published_at']
                                : date('Y-m-d H:i:s'),
            // Admin posts go live immediately; member submissions need approval
            'status'       => $data['status'] ?? 'pending',
        ];

        return $this->repo->create($fields)->toArray();
    }

    /** Return media items pending admin approval. */
    public function getPending(): array
    {
        return array_map(fn($m) => $m->toArray(), $this->repo->findPending());
    }

    /** Approve a media item so it appears publicly. */
    public function approve(int $id): array
    {
        $item = $this->repo->findById($id);
        if (!$item) {
            throw new \InvalidArgumentException("Media #{$id} not found.");
        }
        $this->repo->updateStatus($id, 'approved');
        return ['success' => true];
    }

    /** Reject a media item. */
    public function reject(int $id): array
    {
        $item = $this->repo->findById($id);
        if (!$item) {
            throw new \InvalidArgumentException("Media #{$id} not found.");
        }
        $this->repo->updateStatus($id, 'rejected');
        return ['success' => true];
    }

    /**
     * Delete a media item by ID.
     */
    public function delete(int $id): bool
    {
        $item = $this->repo->findById($id);
        if (!$item) {
            throw new \InvalidArgumentException("Media #{$id} not found.");
        }
        return $this->repo->delete($id);
    }

    /**
     * Update editable fields (video_url, thumbnail, featured, title, description).
     */
    public function update(int $id, array $data): array
    {
        $item = $this->repo->findById($id);
        if (!$item) {
            throw new \InvalidArgumentException("Media #{$id} not found.");
        }

        $fields = [];
        if (isset($data['video_url']))   $fields['video_url']   = trim((string) $data['video_url']);
        if (isset($data['thumbnail']))   $fields['thumbnail']   = trim((string) $data['thumbnail']);
        if (isset($data['title']))       $fields['title']       = trim((string) $data['title']);
        if (isset($data['description'])) $fields['description'] = trim((string) $data['description']);
        if (isset($data['featured']))    $fields['featured']    = (int) (bool) $data['featured'];

        if (empty($fields)) {
            throw new \InvalidArgumentException('No valid fields to update.');
        }

        $this->repo->updateFields($id, $fields);
        return $this->repo->findById($id)->toArray();
    }
}
