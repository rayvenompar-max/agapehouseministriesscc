<?php
/**
 * Front Controller — all web requests start here.
 * Routes to the appropriate page controller or falls back to the SPA shell.
 */
declare(strict_types=1);

// Keep sessions alive for 8 hours (XAMPP default is 24 min — too short)
ini_set('session.gc_maxlifetime', '28800');
ini_set('session.cookie_lifetime', '28800');

session_start(); // Enable sessions for admin authentication

define('BASE_PATH', __DIR__);
define('BASE_URL',  '/DigitalEvangelization');

// Load Composer autoloader for dependencies (PHPMailer, etc.)
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';

// Autoload everything under src/
spl_autoload_register(function (string $class): void {
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ---------- Auth service (available everywhere) ----------
$authService = new \Service\AdminAuthService(
    new \Repository\AdminRepository(getDB())
);

// ---------- Member auth service ----------
$memberAuth = new \Service\MemberAuthService(
    new \Repository\MemberRepository(getDB())
);

// ---------- Routing ----------
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Strip base path prefix
$route = '/' . ltrim(str_replace(BASE_URL, '', $uri), '/');

// API routes — handled before the SPA shell
if (str_starts_with($route, '/api/')) {
    require_once BASE_PATH . '/api/router.php';
    exit;
}

// Admin routes — password-protected panel
if (str_starts_with($route, '/admin')) {
    require_once BASE_PATH . '/admin/index.php';
    exit;
}

// ── Login page ──────────────────────────────────────────────────────────────

// POST /login — process sign-in (admin only)
if ($method === 'POST' && $route === '/login') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $loginError = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($authService->login($username, $password)) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
        $loginError = 'Incorrect username or password.';
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $authTab = 'signin';
    require_once BASE_PATH . '/views/login.php';
    exit;
}

// POST /register — process sign-up
if ($method === 'POST' && $route === '/register') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $registerError = 'Invalid request. Please try again.';
    } else {
        $username    = trim($_POST['username']     ?? '');
        $password    = $_POST['password']          ?? '';
        $confirm     = $_POST['confirm_password']  ?? '';
        $displayName = trim($_POST['display_name'] ?? '');

        if ($displayName === '') {
            $registerError = 'Display name is required.';
        } elseif (strlen($username) < 3) {
            $registerError = 'Username must be at least 3 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $registerError = 'Username may only contain letters, numbers, and underscores.';
        } elseif (strlen($password) < 8) {
            $registerError = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $registerError = 'Passwords do not match.';
        } elseif (!$authService->register($username, $password, $displayName)) {
            $registerError = 'Username already taken. Choose another.';
        } else {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $authTab = 'signup';
    require_once BASE_PATH . '/views/login.php';
    exit;
}

// GET /login — show auth page
if ($route === '/login' || $route === '/register') {
    if ($authService->isLoggedIn()) {
        header('Location: ' . BASE_URL . '/admin');
        exit;
    }
    if ($memberAuth->isLoggedIn()) {
        header('Location: ' . BASE_URL . '/portal');
        exit;
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $authTab = $route === '/register' ? 'signup' : 'signin';
    require_once BASE_PATH . '/views/login.php';
    exit;
}

// POST /logout
if ($method === 'POST' && $route === '/logout') {
    $authService->logout();
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// ── Member auth routes ───────────────────────────────────────────────────────

// POST /member/login — process member sign-in
if ($method === 'POST' && $route === '/member/login') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $loginError = 'Invalid request. Please try again.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';
        if ($memberAuth->login($identifier, $password)) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        $loginError = 'Incorrect email/username or password.';
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $authTab = 'signin';
    require_once BASE_PATH . '/views/login.php';
    exit;
}

// POST /member/register — create member account
if ($method === 'POST' && $route === '/member/register') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $registerError = 'Invalid request. Please try again.';
    } else {
        $email       = trim($_POST['email']          ?? '');
        $username    = trim($_POST['username']        ?? '');
        $password    = $_POST['password']             ?? '';
        $confirm     = $_POST['confirm_password']     ?? '';
        $displayName = trim($_POST['display_name']    ?? '');

        if ($displayName === '') {
            $registerError = 'Full name is required.';
        } elseif (strlen($username) < 3) {
            $registerError = 'Username must be at least 3 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $registerError = 'Username may only contain letters, numbers, and underscores.';
        } elseif (strlen($password) < 8) {
            $registerError = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $registerError = 'Passwords do not match.';
        } else {
            $result = $memberAuth->register($email, $username, $password, $displayName);
            if ($result === true) {
                header('Location: ' . BASE_URL . '/');
                exit;
            }
            $registerError = $result;
        }
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $authTab = 'signup';
    require_once BASE_PATH . '/views/login.php';
    exit;
}

// POST /member/logout
if ($method === 'POST' && $route === '/member/logout') {
    $memberAuth->logout();
    header('Location: ' . BASE_URL . '/member/login');
    exit;
}

// GET /member/login or /member/register — show login page
if ($route === '/member/login' || $route === '/member/register') {
    if ($memberAuth->isLoggedIn()) {
        header('Location: ' . BASE_URL . '/portal');
        exit;
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $authTab = $route === '/member/register' ? 'signup' : 'signin';
    require_once BASE_PATH . '/views/login.php';
    exit;
}

// ── Member portal ────────────────────────────────────────────────────────────
if (str_starts_with($route, '/portal')) {
    if (!$memberAuth->isLoggedIn()) {
        header('Location: ' . BASE_URL . '/member/login');
        exit;
    }
    require_once BASE_PATH . '/views/portal.php';
    exit;
}

// ── Public member profile ─────────────────────────────────────────────────
// GET /member/{username} — view another member's public profile
if ($method === 'GET' && preg_match('#^/member/([a-zA-Z0-9_]+)$#', $route, $m)) {
    $profileUsername = $m[1];
    $memberRepo      = new \Repository\MemberRepository(getDB());
    $profileData     = $memberRepo->findPublicByUsername($profileUsername);
    if (!$profileData) {
        http_response_code(404);
        // Fall through to SPA shell which will show a generic 404 state
    } else {
        require_once BASE_PATH . '/views/member_profile.php';
        exit;
    }
}

// Every other request renders the SPA shell
require_once BASE_PATH . '/views/layout.php';
