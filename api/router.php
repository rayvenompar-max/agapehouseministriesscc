<?php
/**
 * API Router
 *
 * Maps incoming HTTP method + path to the correct controller action.
 * All responses are JSON.
 *
 * Registered routes:
 *   GET    /api/media
 *   GET    /api/media/featured
 *   POST   /api/media
 *   GET    /api/articles
 *   GET    /api/articles/{id}
 *   POST   /api/articles
 *   GET    /api/prayers
 *   GET    /api/prayers/pending
 *   POST   /api/prayers
 *   POST   /api/prayers/{id}/pray
 *   POST   /api/prayers/{id}/approve
 *   POST   /api/prayers/{id}/reject
 *   GET    /api/events/weekly
 *   GET    /api/events/upcoming
 *   POST   /api/events
 *   GET    /api/contact
 *   POST   /api/contact
 *   POST   /api/contact/{id}/read
 */
declare(strict_types=1);

// Global JSON error handler — must be set before any other code runs.
// Catches any uncaught exception or fatal error and returns valid JSON.
set_exception_handler(function (Throwable $e): void {
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    $msg = APP_DEBUG
        ? $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
        : 'An internal server error occurred.';
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
});

// Start session so protected routes can read $_SESSION['member']
if (session_status() === PHP_SESSION_NONE) {
    // Match the lifetime set in index.php (8 hours)
    ini_set('session.gc_maxlifetime', '28800');
    ini_set('session.cookie_lifetime', '28800');
    session_start();
}

// CORS headers (only for cross-origin requests during development)
if (!empty($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---------- Dependency wiring ----------
$db = getDB();

use Controller\MediaController;
use Controller\ArticleController;
use Controller\PrayerController;
use Controller\EventController;
use Controller\ContactController;
use Controller\AnnouncementController;
use Controller\CommentController;
use Controller\NotificationController;
use Controller\DirectMessageController;
use Controller\GalleryController;
use Repository\MediaRepository;
use Repository\ArticleRepository;
use Repository\PrayerRepository;
use Repository\EventRepository;
use Repository\ContactRepository;
use Repository\AnnouncementRepository;
use Repository\CommentRepository;
use Repository\NotificationRepository;
use Repository\MemberRepository;
use Repository\DirectMessageRepository;
use Repository\GalleryRepository;
use Repository\PostLikeRepository;
use Service\MediaService;
use Service\ArticleService;
use Service\PrayerService;
use Service\EventService;
use Service\ContactService;
use Service\AnnouncementService;
use Service\CommentService;
use Service\NotificationService;

$mediaCtrl        = new MediaController(new MediaService(new MediaRepository($db)));
$articleCtrl      = new ArticleController(new ArticleService(new ArticleRepository($db)));
$prayerCtrl       = new PrayerController(new PrayerService(new PrayerRepository($db)));
$eventCtrl        = new EventController(new EventService(new EventRepository($db), new NotificationRepository($db), new MemberRepository($db)));
$contactCtrl      = new ContactController(new ContactService(new ContactRepository($db), new NotificationRepository($db)));
$announcementCtrl = new AnnouncementController(new AnnouncementService(new AnnouncementRepository($db), new NotificationRepository($db), new MemberRepository($db)));
$commentCtrl      = new CommentController(new CommentService(new CommentRepository($db)));
$notifCtrl        = new NotificationController(new NotificationService(new NotificationRepository($db)));
$dmCtrl           = new DirectMessageController(new DirectMessageRepository($db), new MemberRepository($db));
$galleryCtrl      = new GalleryController(new GalleryRepository($db), new NotificationRepository($db), new PostLikeRepository($db));

// ---------- Route matching ----------
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path + /api prefix to get the resource path, e.g. /prayers
$path = preg_replace('#^' . preg_quote(BASE_URL . '/api', '#') . '#', '', $uri);
$path = '/' . ltrim($path ?? '', '/');

// Helper: match a path pattern with named segments, e.g. /articles/{id}
function matchRoute(string $pattern, string $path, array &$params = []): bool
{
    $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';
    if (preg_match($regex, $path, $matches)) {
        foreach ($matches as $k => $v) {
            if (!is_int($k)) {
                $params[$k] = $v;
            }
        }
        return true;
    }
    return false;
}

$params = [];

// ---- Media ----
if ($method === 'GET' && matchRoute('/media/featured', $path)) {
    $mediaCtrl->getFeatured();

} elseif ($method === 'GET' && matchRoute('/media/pending', $path)) {
    $mediaCtrl->getPending();

} elseif ($method === 'POST' && matchRoute('/media/{id}/approve', $path, $params)) {
    $mediaCtrl->approve((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/media/{id}/reject', $path, $params)) {
    $mediaCtrl->reject((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/media/upload', $path)) {
    // Handle video file upload — returns { status, data: { video_url } }
    $uploadDir = BASE_PATH . '/public/uploads/videos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    if (empty($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        $errCode = $_FILES['video']['error'] ?? -1;
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Upload failed (code {$errCode})."]);
        exit;
    }

    $file     = $_FILES['video'];
    $allowed  = ['video/mp4', 'video/quicktime', 'video/webm', 'video/x-msvideo', 'video/avi', 'video/ogg'];
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mime     = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Invalid file type: {$mime}"]);
        exit;
    }

    $maxBytes = 500 * 1024 * 1024; // 500 MB
    if ($file['size'] > $maxBytes) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'File exceeds the 500 MB limit.']);
        exit;
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'mp4';
    $safeName = bin2hex(random_bytes(16)) . '.' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ext));
    $dest     = $uploadDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Could not save uploaded file.']);
        exit;
    }

    $videoUrl = BASE_URL . '/public/uploads/videos/' . $safeName;
    echo json_encode(['status' => 'success', 'data' => ['video_url' => $videoUrl]]);
    exit;

} elseif ($method === 'PATCH' && matchRoute('/media/{id}', $path, $params)) {
    $mediaCtrl->update((int) $params['id']);

} elseif ($method === 'DELETE' && matchRoute('/media/{id}', $path, $params)) {
    $mediaCtrl->delete((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/media/{id}', $path, $params)) {
    $mediaCtrl->getOne((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/media', $path)) {
    $mediaCtrl->getListing();

} elseif ($method === 'POST' && matchRoute('/media', $path)) {
    $mediaCtrl->create();

// ---- Articles ----
} elseif ($method === 'GET' && matchRoute('/articles/pending', $path)) {
    $articleCtrl->getPending();

} elseif ($method === 'POST' && matchRoute('/articles/{id}/approve', $path, $params)) {
    $articleCtrl->approve((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/articles/{id}/reject', $path, $params)) {
    $articleCtrl->reject((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/articles/{id}', $path, $params)) {
    $articleCtrl->getOne((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/articles', $path)) {
    $articleCtrl->getAll();

} elseif ($method === 'POST' && matchRoute('/articles', $path)) {
    $articleCtrl->create();

// ---- Gallery ----
} elseif ($method === 'GET' && matchRoute('/gallery/pending', $path)) {
    $galleryCtrl->getPending();

} elseif ($method === 'POST' && matchRoute('/gallery/{id}/approve', $path, $params)) {
    $galleryCtrl->approve((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/gallery/{id}/reject', $path, $params)) {
    $galleryCtrl->reject((int) $params['id']);

} elseif ($method === 'DELETE' && matchRoute('/gallery/{id}', $path, $params)) {
    $galleryCtrl->delete((int) $params['id']);

} elseif ($method === 'PUT' && matchRoute('/gallery/{id}', $path, $params)) {
    $galleryCtrl->update((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/gallery/{id}', $path, $params)) {
    $galleryCtrl->getOne((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/gallery', $path)) {
    $galleryCtrl->getApproved();

} elseif ($method === 'POST' && matchRoute('/gallery', $path)) {
    $galleryCtrl->create();

} elseif ($method === 'POST' && matchRoute('/gallery/upload', $path)) {
    // Handle image file upload
    $uploadDir = BASE_PATH . '/public/uploads/gallery/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errCode = $_FILES['image']['error'] ?? -1;
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Upload failed (code {$errCode})."]);
        exit;
    }

    $file     = $_FILES['image'];
    $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mime     = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed."]);
        exit;
    }

    $maxBytes = 10 * 1024 * 1024; // 10 MB
    if ($file['size'] > $maxBytes) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Image exceeds the 10 MB limit.']);
        exit;
    }

    $extMap   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $ext      = $extMap[$mime];
    $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest     = $uploadDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Could not save uploaded file.']);
        exit;
    }

    $imageUrl = BASE_URL . '/public/uploads/gallery/' . $safeName;
    echo json_encode(['status' => 'success', 'data' => ['image_url' => $imageUrl]]);
    exit;

// ---- Prayers ----
} elseif ($method === 'POST' && matchRoute('/prayers/{id}/approve', $path, $params)) {
    $prayerCtrl->approve((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/prayers/{id}/reject', $path, $params)) {
    $prayerCtrl->reject((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/prayers/{id}/pray', $path, $params)) {
    $prayerCtrl->pray((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/prayers/pending', $path)) {
    $prayerCtrl->getPending();

} elseif ($method === 'GET' && matchRoute('/prayers', $path)) {
    $prayerCtrl->getWall();

} elseif ($method === 'POST' && matchRoute('/prayers', $path)) {
    $prayerCtrl->submit();

// ---- Events ----
} elseif ($method === 'GET' && matchRoute('/events/weekly', $path)) {
    $eventCtrl->getWeekly();

} elseif ($method === 'GET' && matchRoute('/events/upcoming', $path)) {
    $eventCtrl->getUpcoming();

} elseif ($method === 'GET' && matchRoute('/events/all', $path)) {
    $eventCtrl->getAllWithCounts();

} elseif ($method === 'POST' && matchRoute('/events/{id}/register', $path, $params)) {
    $eventCtrl->register((int) $params['id']);

} elseif ($method === 'DELETE' && matchRoute('/events/{id}/register', $path, $params)) {
    $eventCtrl->cancelRegistration((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/events/{id}/register', $path, $params)) {
    $eventCtrl->getRegistrationStatus((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/events/{id}/registrations', $path, $params)) {
    $eventCtrl->getRegistrations((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/events', $path)) {
    $eventCtrl->create();

} elseif ($method === 'DELETE' && matchRoute('/events/{id}', $path, $params)) {
    $eventCtrl->deleteEvent((int) $params['id']);

// ---- Contact ----
} elseif ($method === 'GET' && matchRoute('/contact/threads', $path)) {
    $contactCtrl->getThreads();

} elseif ($method === 'GET' && matchRoute('/contact', $path)) {
    $contactCtrl->list();

} elseif ($method === 'POST' && matchRoute('/contact/{id}/read', $path, $params)) {
    $contactCtrl->markRead((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/contact/{id}/reply', $path, $params)) {
    $contactCtrl->adminReply((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/contact/{id}/thread', $path, $params)) {
    $contactCtrl->getThread((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/contact/{id}/message', $path, $params)) {
    $contactCtrl->memberReply((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/contact', $path)) {
    $contactCtrl->send();

// ---- Direct Messages (Member to Member) ----
} elseif ($method === 'POST' && matchRoute('/messages/start/{memberId}', $path, $params)) {
    $dmCtrl->startConversation((int) $params['memberId']);

} elseif ($method === 'GET' && matchRoute('/messages/conversations', $path)) {
    $dmCtrl->getConversations();

} elseif ($method === 'POST' && matchRoute('/messages/conversation/{id}/read', $path, $params)) {
    $dmCtrl->markAsRead((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/messages/conversation/{id}', $path, $params)) {
    $dmCtrl->getConversation((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/messages/conversation/{id}', $path, $params)) {
    $dmCtrl->sendMessage((int) $params['id']);

// ---- Announcements ----
} elseif ($method === 'GET' && matchRoute('/announcements/pinned', $path)) {
    $announcementCtrl->getPinned();

} elseif ($method === 'GET' && matchRoute('/announcements/{id}', $path, $params)) {
    $announcementCtrl->getOne((int) $params['id']);

} elseif ($method === 'GET' && matchRoute('/announcements', $path)) {
    $announcementCtrl->getAll();

} elseif ($method === 'POST' && matchRoute('/announcements', $path)) {
    $announcementCtrl->create();

} elseif ($method === 'PATCH' && matchRoute('/announcements/{id}', $path, $params)) {
    $announcementCtrl->update((int) $params['id']);

} elseif ($method === 'DELETE' && matchRoute('/announcements/{id}', $path, $params)) {
    $announcementCtrl->delete((int) $params['id']);

// ---- Member profile ----
} elseif ($method === 'GET' && matchRoute('/members/all', $path)) {
    // Get all members for admin panel
    if (empty($_SESSION['admin']['id']) && empty($_SESSION['admin_logged_in'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Admin authentication required.']);
        exit;
    }
    
    try {
        $memberRepo = new \Repository\MemberRepository($db);
        $members = $memberRepo->getAllMembersForAdmin(100, 0);
        
        echo json_encode(['status' => 'success', 'data' => $members]);
    } catch (\Throwable $e) {
        error_log('Get all members error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Failed to load members: ' . $e->getMessage()
        ]);
    }
    exit;

} elseif ($method === 'PATCH' && matchRoute('/members/{id}/status', $path, $params)) {
    // Update member status
    if (empty($_SESSION['admin']['id']) && empty($_SESSION['admin_logged_in'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Admin authentication required.']);
        exit;
    }
    
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $status = trim((string)($body['status'] ?? ''));
    
    if (!in_array($status, ['active', 'inactive', 'banned'], true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid status. Must be active, inactive, or banned.']);
        exit;
    }
    
    try {
        $memberRepo = new \Repository\MemberRepository($db);
        $memberRepo->updateStatus((int) $params['id'], $status);
        
        echo json_encode(['status' => 'success', 'message' => 'User status updated successfully.']);
    } catch (\Throwable $e) {
        error_log('Update member status error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Failed to update status: ' . $e->getMessage()
        ]);
    }
    exit;

} elseif ($method === 'POST' && matchRoute('/members/{id}/reset-password', $path, $params)) {
    // Admin reset user password
    if (empty($_SESSION['admin']['id']) && empty($_SESSION['admin_logged_in'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Admin authentication required.']);
        exit;
    }
    
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $newPassword = trim((string)($body['new_password'] ?? ''));
    
    if (strlen($newPassword) < 8) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters.']);
        exit;
    }
    
    try {
        $memberRepo = new \Repository\MemberRepository($db);
        $member = $memberRepo->findById((int) $params['id']);
        
        if (!$member) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Member not found.']);
            exit;
        }
        
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $memberRepo->updatePassword((int) $params['id'], $newHash);
        
        // Send email notification with new password
        try {
            $emailService = new \Service\EmailService();
            $emailConfig = require BASE_PATH . '/config/email.php';
            $siteUrl = $emailConfig['site_url'] ?? BASE_URL;
            
            $subject = 'Your Password Has Been Reset';
            
            $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 30px 20px; }
        .message { font-size: 16px; margin-bottom: 20px; color: #555; }
        .password-box { background: #f8f9fa; border: 2px solid #667eea; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .password-label { font-size: 14px; color: #666; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
        .password-value { font-size: 24px; font-weight: bold; color: #667eea; font-family: 'Courier New', monospace; letter-spacing: 2px; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .warning-box strong { color: #856404; }
        .button-container { text-align: center; margin: 30px 0; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: 600; }
        .button:hover { background: #5568d3; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #e9ecef; }
        .footer a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Reset</h1>
        </div>
        <div class="content">
            <p class="message">Hello <strong>{$member->displayName}</strong>,</p>
            <p class="message">Your password reset request has been processed by our administrator. Your new temporary password is:</p>
            
            <div class="password-box">
                <div class="password-label">Your New Password</div>
                <div class="password-value">{$newPassword}</div>
            </div>
            
            <div class="warning-box">
                <strong>⚠️ Important Security Notice:</strong><br>
                For your security, please log in and change this temporary password immediately.
            </div>
            
            <div class="button-container">
                <a href="{$siteUrl}/?page=login" class="button">Log In Now</a>
            </div>
            
            <p style="font-size: 14px; color: #666; margin-top: 20px;">
                <strong>Security Tips:</strong><br>
                • Change your password after logging in<br>
                • Use a strong, unique password<br>
                • Never share your password with anyone
            </p>
        </div>
        <div class="footer">
            <p>This password reset was performed by an administrator at Agape House Ministries</p>
            <p>If you did not request this reset, please contact us immediately at <a href="{$siteUrl}/?page=contact">our contact page</a></p>
            <p>Visit us at <a href="{$siteUrl}">Agape House Ministries</a></p>
        </div>
    </div>
</body>
</html>
HTML;
            
            $textBody = <<<TEXT
Password Reset - Agape House Ministries

Hello {$member->displayName},

Your password reset request has been processed by our administrator.

Your New Password: {$newPassword}

⚠️ IMPORTANT: For your security, please log in and change this temporary password immediately.

Log in here: {$siteUrl}/?page=login

Security Tips:
• Change your password after logging in
• Use a strong, unique password
• Never share your password with anyone

---
This password reset was performed by an administrator at Agape House Ministries.
If you did not request this reset, please contact us immediately.
TEXT;
            
            $emailService->sendNotificationEmail(
                $member->email,
                $member->displayName,
                $subject,
                $htmlBody,
                $textBody
            );
        } catch (\Throwable $emailError) {
            // Log email error but don't fail the password reset
            error_log('Failed to send password reset email: ' . $emailError->getMessage());
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Password reset successfully. User has been notified via email.']);
    } catch (\Throwable $e) {
        error_log('Reset password error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Failed to reset password: ' . $e->getMessage()
        ]);
    }
    exit;

} elseif ($method === 'GET' && matchRoute('/member/search', $path)) {
    // Search for members by username or display name
    $query = trim((string)($_GET['q'] ?? ''));
    if ($query === '') {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }
    
    try {
        $memberRepo = new \Repository\MemberRepository($db);
        $currentMemberId = (int)($_SESSION['member']['id'] ?? 0);
        $results = $memberRepo->searchMembers($query, $currentMemberId, 10);
        
        echo json_encode(['status' => 'success', 'data' => $results]);
    } catch (\Throwable $e) {
        error_log('Member search error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Search failed: ' . $e->getMessage()
        ]);
    }
    exit;

} elseif ($method === 'POST' && matchRoute('/member/avatar', $path)) {
    // Upload profile picture for the logged-in member
    if (empty($_SESSION['member']['id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
        exit;
    }

    $uploadDir = BASE_PATH . '/public/uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $errCode = $_FILES['avatar']['error'] ?? -1;
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Upload failed (code {$errCode})."]);
        exit;
    }

    $file    = $_FILES['avatar'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo   = finfo_open(FILEINFO_MIME_TYPE);
    $mime    = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Only JPEG, PNG, GIF, or WebP images are allowed.']);
        exit;
    }

    $maxBytes = 5 * 1024 * 1024; // 5 MB
    if ($file['size'] > $maxBytes) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Image must be 5 MB or smaller.']);
        exit;
    }

    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $ext    = $extMap[$mime];
    $name   = 'avatar_' . (int)$_SESSION['member']['id'] . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest   = $uploadDir . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Could not save the image. Please try again.']);
        exit;
    }

    $relativePath = BASE_URL . '/public/uploads/avatars/' . $name;

    $memberRepo = new \Repository\MemberRepository($db);
    $memberRepo->updateProfilePicture((int) $_SESSION['member']['id'], $relativePath);
    $_SESSION['member']['profile_picture'] = $relativePath;

    echo json_encode(['status' => 'success', 'data' => ['profile_picture' => $relativePath]]);
    exit;

} elseif ($method === 'GET' && matchRoute('/member/profile', $path)) {
    // Return current logged-in member profile (from session)
    if (empty($_SESSION['member']['id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
        exit;
    }
    $memberRepo   = new \Repository\MemberRepository($db);
    $memberRecord = $memberRepo->findById((int) $_SESSION['member']['id']);
    if (!$memberRecord) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Member not found.']);
        exit;
    }
    echo json_encode(['status' => 'success', 'data' => [
        'id'              => $memberRecord->id,
        'display_name'    => $memberRecord->displayName,
        'username'        => $memberRecord->username,
        'email'           => $memberRecord->email,
        'profile_picture' => $memberRecord->profilePicture,
        'member_since'    => date('F j, Y', strtotime($memberRecord->createdAt)),
        'last_login'      => $memberRecord->lastLogin
            ? date('F j, Y', strtotime($memberRecord->lastLogin))
            : 'Never',
        'following_count' => $memberRepo->countFollowing($memberRecord->id),
        'follower_count'  => $memberRepo->countFollowers($memberRecord->id),
    ]]);
    exit;

} elseif ($method === 'GET' && preg_match('#^/member/public/([a-zA-Z0-9_]+)$#', $path, $matches)) {
    // Return public profile data for any member by username
    $username   = $matches[1];
    $memberRepo = new \Repository\MemberRepository($db);
    $memberRecord = $memberRepo->findByUsername($username);
    
    if (!$memberRecord) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Member not found.']);
        exit;
    }
    
    // Only return safe public fields
    echo json_encode(['status' => 'success', 'data' => [
        'id'              => $memberRecord->id,
        'display_name'    => $memberRecord->displayName,
        'username'        => $memberRecord->username,
        'profile_picture' => $memberRecord->profilePicture,
        'created_at'      => $memberRecord->createdAt,
    ]]);
    exit;

} elseif ($method === 'PATCH' && matchRoute('/member/profile', $path)) {
    // Update display name for the logged-in member (legacy — kept for backward compat)
    if (empty($_SESSION['member']['id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
        exit;
    }
    $body        = json_decode(file_get_contents('php://input'), true) ?? [];
    $displayName = trim((string)($body['display_name'] ?? ''));
    if ($displayName === '' || strlen($displayName) > 120) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Display name must be 1–120 characters.']);
        exit;
    }
    $memberRepo = new \Repository\MemberRepository($db);
    $memberRepo->updateDisplayName((int) $_SESSION['member']['id'], $displayName);
    // Update session so the header reflects the change immediately
    $_SESSION['member']['display_name'] = $displayName;
    echo json_encode(['status' => 'success', 'data' => ['display_name' => $displayName]]);
    exit;

// ---- Member full profile update (display name + username + email + optional password) ----
} elseif ($method === 'POST' && matchRoute('/member/profile/update', $path)) {
    if (empty($_SESSION['member']['id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
        exit;
    }

    $body        = json_decode(file_get_contents('php://input'), true) ?? [];
    $memberId    = (int) $_SESSION['member']['id'];
    $displayName = trim((string)($body['display_name'] ?? ''));
    $username    = trim((string)($body['username']     ?? ''));
    $email       = trim(strtolower((string)($body['email'] ?? '')));
    $currentPass = (string)($body['current_password'] ?? '');
    $newPass     = (string)($body['new_password']     ?? '');
    $confirmPass = (string)($body['confirm_password'] ?? '');

    // ── Validation ──
    if ($displayName === '' || strlen($displayName) > 120) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Display name must be 1–120 characters.']);
        exit;
    }
    if ($username === '' || strlen($username) > 60 || !preg_match('/^[a-z0-9_]+$/i', $username)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Username must be 1–60 characters and contain only letters, numbers, and underscores.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 160) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'A valid email address is required.']);
        exit;
    }

    $memberRepo = new \Repository\MemberRepository($db);

    if ($memberRepo->usernameTakenByOther($memberId, $username)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'That username is already taken.']);
        exit;
    }
    if ($memberRepo->emailTakenByOther($memberId, $email)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'An account with that email already exists.']);
        exit;
    }

    // ── Password change (optional — only if new password was provided) ──
    $changingPassword = ($newPass !== '' || $confirmPass !== '');
    if ($changingPassword) {
        if ($currentPass === '') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Current password is required to set a new password.']);
            exit;
        }
        if (strlen($newPass) < 8) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'New password must be at least 8 characters.']);
            exit;
        }
        if ($newPass !== $confirmPass) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'New passwords do not match.']);
            exit;
        }
    }

    // ── Current password required when changing email or username ──
    $currentMember   = $memberRepo->findById($memberId);
    $emailChanged    = $currentMember && strtolower($currentMember->email)    !== $email;
    $usernameChanged = $currentMember && strtolower($currentMember->username) !== strtolower($username);

    if (($emailChanged || $usernameChanged || $changingPassword) && $currentPass === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Enter your current password to save these changes.']);
        exit;
    }
    if (($emailChanged || $usernameChanged || $changingPassword) && $currentPass !== '') {
        if (!$currentMember || !password_verify($currentPass, $currentMember->password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
            exit;
        }
    }

    // ── Apply updates ──
    $memberRepo->updateDisplayName($memberId, $displayName);
    $memberRepo->updateEmail($memberId, $email);
    $memberRepo->updateUsername($memberId, $username);
    if ($changingPassword) {
        $memberRepo->updatePassword($memberId, password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]));
    }

    // Update session
    $_SESSION['member']['display_name'] = $displayName;
    $_SESSION['member']['username']     = $username;
    $_SESSION['member']['email']        = $email;

    echo json_encode(['status' => 'success', 'data' => [
        'display_name' => $displayName,
        'username'     => $username,
        'email'        => $email,
    ]]);
    exit;

// ---- Member change password ----
} elseif ($method === 'POST' && matchRoute('/member/change-password', $path)) {
    if (empty($_SESSION['member']['id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
        exit;
    }
    $body        = json_decode(file_get_contents('php://input'), true) ?? [];
    $currentPass = (string)($body['current_password'] ?? '');
    $newPass     = (string)($body['new_password']     ?? '');
    $confirmPass = (string)($body['confirm_password'] ?? '');

    if ($currentPass === '' || $newPass === '' || $confirmPass === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'All password fields are required.']);
        exit;
    }
    if (strlen($newPass) < 8) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'New password must be at least 8 characters.']);
        exit;
    }
    if ($newPass !== $confirmPass) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'New passwords do not match.']);
        exit;
    }
    $memberRepo   = new \Repository\MemberRepository($db);
    $memberRecord = $memberRepo->findById((int) $_SESSION['member']['id']);
    if (!$memberRecord || !password_verify($currentPass, $memberRecord->password)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
        exit;
    }
    $newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
    $memberRepo->updatePassword((int) $_SESSION['member']['id'], $newHash);
    echo json_encode(['status' => 'success', 'message' => 'Password changed successfully.']);
    exit;

// ---- Member follows ----
} elseif ($method === 'POST' && matchRoute('/member/{id}/follow', $path, $params)) {
    if (empty($_SESSION['member']['id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
        exit;
    }
    $followerId  = (int) $_SESSION['member']['id'];
    $followingId = (int) $params['id'];
    if ($followerId === $followingId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'You cannot follow yourself.']);
        exit;
    }
    $memberRepo = new \Repository\MemberRepository($db);
    $memberRepo->follow($followerId, $followingId);
    echo json_encode([
        'status' => 'success',
        'data'   => [
            'following'        => true,
            'follower_count'   => $memberRepo->countFollowers($followingId),
            'following_count'  => $memberRepo->countFollowing($followerId),
        ],
    ]);
    exit;

} elseif ($method === 'DELETE' && matchRoute('/member/{id}/follow', $path, $params)) {
    if (empty($_SESSION['member']['id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
        exit;
    }
    $followerId  = (int) $_SESSION['member']['id'];
    $followingId = (int) $params['id'];
    $memberRepo  = new \Repository\MemberRepository($db);
    $memberRepo->unfollow($followerId, $followingId);
    echo json_encode([
        'status' => 'success',
        'data'   => [
            'following'        => false,
            'follower_count'   => $memberRepo->countFollowers($followingId),
            'following_count'  => $memberRepo->countFollowing($followerId),
        ],
    ]);
    exit;

} elseif ($method === 'GET' && matchRoute('/member/{id}/follow', $path, $params)) {
    // Returns follow status + counts for member $id relative to the logged-in user
    $targetId   = (int) $params['id'];
    $memberRepo = new \Repository\MemberRepository($db);
    $isFollowing = !empty($_SESSION['member']['id'])
        ? $memberRepo->isFollowing((int) $_SESSION['member']['id'], $targetId)
        : false;
    echo json_encode([
        'status' => 'success',
        'data'   => [
            'following'       => $isFollowing,
            'follower_count'  => $memberRepo->countFollowers($targetId),
            'following_count' => $memberRepo->countFollowing($targetId),
        ],
    ]);
    exit;

// ---- Post Likes ----
} elseif ($method === 'POST' && matchRoute('/likes/{type}/{id}', $path, $params)) {
    // Toggle like — requires member login
    if (empty($_SESSION['member']['id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
        exit;
    }
    $validTypes = ['article', 'media', 'announcement', 'gallery'];
    $targetType = $params['type'];
    $targetId   = (int) $params['id'];
    if (!in_array($targetType, $validTypes, true) || $targetId <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid target.']);
        exit;
    }
    $memberId    = (int) $_SESSION['member']['id'];
    $likeRepo    = new \Repository\PostLikeRepository($db);
    $nowLiked    = $likeRepo->toggle($memberId, $targetType, $targetId);
    $likeCount   = $likeRepo->countFor($targetType, $targetId);

    // Notification: fire when liking, remove when unliking
    $notifRepo  = new \Repository\NotificationRepository($db);
    // Resolve post author
    $tableMap   = ['article' => 'articles', 'media' => 'media', 'announcement' => 'announcements', 'gallery' => 'gallery'];
    $table      = $tableMap[$targetType];
    $authorStmt = $db->prepare("SELECT member_id, title FROM {$table} WHERE id = :id LIMIT 1");
    $authorStmt->execute(['id' => $targetId]);
    $postRow    = $authorStmt->fetch(\PDO::FETCH_ASSOC);
    $authorId   = (int) ($postRow['member_id'] ?? 0);
    $postTitle  = $postRow['title'] ?? '';

    if ($authorId > 0) {
        if ($nowLiked) {
            $notifRepo->create($authorId, $memberId, 'like', $targetType, $targetId, $postTitle);
        } else {
            $notifRepo->deleteLike($authorId, $memberId, $targetType, $targetId);
        }
    }

    echo json_encode(['status' => 'success', 'data' => [
        'liked'      => $nowLiked,
        'like_count' => $likeCount,
    ]]);
    exit;

} elseif ($method === 'GET' && matchRoute('/likes/{type}/{id}', $path, $params)) {
    // Get like status + count for a target (works for guests too)
    $validTypes = ['article', 'media', 'announcement', 'gallery'];
    $targetType = $params['type'];
    $targetId   = (int) $params['id'];
    if (!in_array($targetType, $validTypes, true) || $targetId <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid target.']);
        exit;
    }
    $likeRepo  = new \Repository\PostLikeRepository($db);
    $memberId  = !empty($_SESSION['member']['id']) ? (int) $_SESSION['member']['id'] : 0;
    $liked     = $memberId > 0 && $likeRepo->hasLiked($memberId, $targetType, $targetId);
    $likeCount = $likeRepo->countFor($targetType, $targetId);
    echo json_encode(['status' => 'success', 'data' => [
        'liked'      => $liked,
        'like_count' => $likeCount,
    ]]);
    exit;

// ---- Comments ----
} elseif ($method === 'GET' && matchRoute('/comments/{type}/{id}', $path, $params)) {
    $commentCtrl->getForTarget($params['type'], (int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/comments/{id}/like', $path, $params)) {
    $commentCtrl->like((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/comments', $path)) {
    $commentCtrl->create();

} elseif ($method === 'DELETE' && matchRoute('/comments/{id}', $path, $params)) {
    $commentCtrl->delete((int) $params['id']);

// ---- Notifications ----
} elseif ($method === 'GET' && matchRoute('/notifications/unread-count', $path)) {
    $notifCtrl->unreadCount();

} elseif ($method === 'POST' && matchRoute('/notifications/read-all', $path)) {
    $notifCtrl->readAll();

} elseif ($method === 'POST' && matchRoute('/notifications/clear-all', $path)) {
    $notifCtrl->clearAll();

} elseif ($method === 'POST' && matchRoute('/notifications/{id}/read', $path, $params)) {
    $notifCtrl->readOne((int) $params['id']);

} elseif ($method === 'POST' && matchRoute('/notifications/like', $path)) {
    $notifCtrl->like();

} elseif ($method === 'POST' && matchRoute('/notifications/share', $path)) {
    $notifCtrl->share();

} elseif ($method === 'POST' && matchRoute('/notifications/comment', $path)) {
    $notifCtrl->comment();

} elseif ($method === 'POST' && matchRoute('/notifications/comment-like', $path)) {
    $notifCtrl->commentLike();

} elseif ($method === 'POST' && matchRoute('/notifications/comment-reply', $path)) {
    $notifCtrl->commentReply();

} elseif ($method === 'POST' && matchRoute('/notifications/follow', $path)) {
    $notifCtrl->follow();

} elseif ($method === 'DELETE' && matchRoute('/notifications/follow', $path)) {
    $notifCtrl->removeFollow();

} elseif ($method === 'GET' && matchRoute('/notifications', $path)) {
    $notifCtrl->index();

// ---- 404 ----
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => "Route not found: {$method} /api{$path}"]);
}
