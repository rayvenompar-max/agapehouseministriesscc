<?php
declare(strict_types=1);

namespace Controller;

use Repository\GalleryRepository;
use Repository\NotificationRepository;
use Repository\PostLikeRepository;

class GalleryController extends BaseController
{
    private GalleryRepository $galleryRepo;
    private NotificationRepository $notifRepo;
    private PostLikeRepository $likeRepo;

    public function __construct(
        GalleryRepository $galleryRepo, 
        NotificationRepository $notifRepo,
        PostLikeRepository $likeRepo
    )
    {
        $this->galleryRepo = $galleryRepo;
        $this->notifRepo = $notifRepo;
        $this->likeRepo = $likeRepo;
    }

    /**
     * GET /api/gallery - Get all approved gallery items
     */
    public function getApproved(): void
    {
        $limit = (int) ($_GET['limit'] ?? 50);
        $offset = (int) ($_GET['offset'] ?? 0);
        
        $items = $this->galleryRepo->getApproved($limit, $offset);
        
        // Enhance with like counts and format for feed
        foreach ($items as &$item) {
            $item['like_count'] = $this->getLikeCount('gallery', (int) $item['id']);
            
            // Add user's like status if logged in
            if (!empty($_SESSION['member']['id'])) {
                $item['liked_by_me'] = $this->isLikedByUser('gallery', (int) $item['id'], (int) $_SESSION['member']['id']);
            } else {
                $item['liked_by_me'] = false;
            }
            
            // Format for feed compatibility
            $item['posted_by'] = $item['display_name'] ?? $item['username'] ?? 'Member';
            $item['poster_username'] = $item['username'] ?? null;
            $item['poster_picture'] = $item['profile_picture'] ?? null;
            
            // Convert images array to image_urls for frontend
            if (!empty($item['images'])) {
                $item['image_urls'] = array_column($item['images'], 'image_url');
            } else {
                $item['image_urls'] = !empty($item['image_url']) ? [$item['image_url']] : [];
            }
        }
        
        $this->json(['status' => 'success', 'data' => $items]);
    }

    /**
     * GET /api/gallery/pending - Get pending gallery items (admin only)
     */
    public function getPending(): void
    {
        $this->requireAdmin();
        
        $items = $this->galleryRepo->getPending();
        $this->json(['status' => 'success', 'data' => $items]);
    }

    /**
     * GET /api/gallery/{id} - Get single gallery item
     */
    public function getOne(int $id): void
    {
        $item = $this->galleryRepo->findById($id);
        
        if (!$item) {
            $this->json(['status' => 'error', 'message' => 'Gallery item not found.'], 404);
            return;
        }
        
        // Enhance with like count
        $item['like_count'] = $this->getLikeCount('gallery', $id);
        
        if (!empty($_SESSION['member']['id'])) {
            $item['liked_by_me'] = $this->isLikedByUser('gallery', $id, (int) $_SESSION['member']['id']);
        } else {
            $item['liked_by_me'] = false;
        }
        
        $this->json(['status' => 'success', 'data' => $item]);
    }

    /**
     * POST /api/gallery - Submit a new gallery item with multiple images
     */
    public function create(): void
    {
        $this->requireMember();
        
        $memberId = (int) $_SESSION['member']['id'];
        $body = $this->getJsonBody();
        
        $title = trim((string) ($body['title'] ?? ''));
        $description = trim((string) ($body['description'] ?? ''));
        $imageUrls = $body['image_urls'] ?? [];
        
        // Support old single image format for backward compatibility
        if (empty($imageUrls) && !empty($body['image_url'])) {
            $imageUrls = [trim((string) $body['image_url'])];
        }
        
        // Validation
        if (empty($imageUrls)) {
            $this->json(['status' => 'error', 'message' => 'At least one image is required.'], 400);
            return;
        }
        
        if (count($imageUrls) > 10) {
            $this->json(['status' => 'error', 'message' => 'Maximum 10 images allowed per post.'], 400);
            return;
        }
        
        if ($title === '' || strlen($title) > 200) {
            $this->json(['status' => 'error', 'message' => 'Title is required (max 200 characters).'], 400);
            return;
        }
        
        if (strlen($description) > 1000) {
            $this->json(['status' => 'error', 'message' => 'Description is too long (max 1000 characters).'], 400);
            return;
        }
        
        $galleryId = $this->galleryRepo->create($memberId, $title, $description, $imageUrls);
        
        $this->json([
            'status' => 'success',
            'message' => 'Your photo(s) have been submitted and are awaiting approval.',
            'data' => ['id' => $galleryId]
        ], 201);
    }

    /**
     * POST /api/gallery/{id}/approve - Approve a gallery item (admin only)
     */
    public function approve(int $id): void
    {
        $this->requireAdmin();
        
        $item = $this->galleryRepo->findById($id);
        if (!$item) {
            $this->json(['status' => 'error', 'message' => 'Gallery item not found.'], 404);
            return;
        }
        
        $this->galleryRepo->approve($id);
        
        // Notify the member that their submission was approved
        $this->notifRepo->createBroadcast(
            (int) $item['member_id'],
            'gallery_approved',
            'gallery',
            $id,
            $item['title']
        );
        
        $this->json([
            'status' => 'success',
            'message' => 'Gallery item approved.'
        ]);
    }

    /**
     * POST /api/gallery/{id}/reject - Reject a gallery item (admin only)
     */
    public function reject(int $id): void
    {
        $this->requireAdmin();
        
        $item = $this->galleryRepo->findById($id);
        if (!$item) {
            $this->json(['status' => 'error', 'message' => 'Gallery item not found.'], 404);
            return;
        }
        
        $this->galleryRepo->reject($id);
        
        // Optionally notify the member
        $this->notifRepo->createBroadcast(
            (int) $item['member_id'],
            'gallery_rejected',
            'gallery',
            $id,
            $item['title']
        );
        
        $this->json([
            'status' => 'success',
            'message' => 'Gallery item rejected.'
        ]);
    }

    /**
     * PUT /api/gallery/{id} - Update a gallery item (owner only)
     */
    public function update(int $id): void
    {
        $this->requireMember();
        
        $item = $this->galleryRepo->findById($id);
        if (!$item) {
            $this->json(['status' => 'error', 'message' => 'Gallery item not found.'], 404);
            return;
        }
        
        // Check ownership
        $memberId = (int) $_SESSION['member']['id'];
        if ((int) $item['member_id'] !== $memberId) {
            $this->json(['status' => 'error', 'message' => 'You can only edit your own posts.'], 403);
            return;
        }
        
        $body = $this->getJsonBody();
        $title = trim((string) ($body['title'] ?? ''));
        $description = trim((string) ($body['description'] ?? ''));
        
        // Validation
        if ($title === '' || strlen($title) > 200) {
            $this->json(['status' => 'error', 'message' => 'Title is required (max 200 characters).'], 400);
            return;
        }
        
        if (strlen($description) > 1000) {
            $this->json(['status' => 'error', 'message' => 'Description is too long (max 1000 characters).'], 400);
            return;
        }
        
        $this->galleryRepo->update($id, $title, $description);
        
        $this->json([
            'status' => 'success',
            'message' => 'Gallery item updated.'
        ]);
    }

    /**
     * DELETE /api/gallery/{id} - Delete a gallery item (owner or admin)
     */
    public function delete(int $id): void
    {
        // Check if user is logged in (member or admin)
        $isMember = !empty($_SESSION['member']['id']);
        $isAdmin = !empty($_SESSION['admin']['id']) || !empty($_SESSION['admin_logged_in']);
        
        if (!$isMember && !$isAdmin) {
            $this->json(['status' => 'error', 'message' => 'Authentication required.'], 401);
            return;
        }
        
        $item = $this->galleryRepo->findById($id);
        if (!$item) {
            $this->json(['status' => 'error', 'message' => 'Gallery item not found.'], 404);
            return;
        }
        
        // Check ownership (members can only delete their own, admins can delete any)
        if ($isMember && !$isAdmin) {
            $memberId = (int) $_SESSION['member']['id'];
            if ((int) $item['member_id'] !== $memberId) {
                $this->json(['status' => 'error', 'message' => 'You can only delete your own posts.'], 403);
                return;
            }
        }
        
        $this->galleryRepo->delete($id);
        
        $this->json([
            'status' => 'success',
            'message' => 'Gallery item deleted.'
        ]);
    }

    /**
     * Helper: Get like count for a gallery item
     */
    private function getLikeCount(string $type, int $id): int
    {
        return $this->likeRepo->countFor($type, $id);
    }

    /**
     * Helper: Check if user liked an item
     */
    private function isLikedByUser(string $type, int $id, int $memberId): bool
    {
        return $this->likeRepo->hasLiked($memberId, $type, $id);
    }

    /**
     * Helper: Require member login
     */
    private function requireMember(): void
    {
        if (empty($_SESSION['member']['id'])) {
            $this->json(['status' => 'error', 'message' => 'Please log in to submit photos.'], 401);
            exit;
        }
    }

    /**
     * Helper: Require admin login
     */
    private function requireAdmin(): void
    {
        $isAdmin = (!empty($_SESSION['admin']['id']))
                || (!empty($_SESSION['admin_logged_in']));
        
        if (!$isAdmin) {
            $this->json(['status' => 'error', 'message' => 'Admin access required.'], 403);
            exit;
        }
    }
}
