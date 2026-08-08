<?php
/**
 * Email Notification Test Script
 * 
 * This script tests your email configuration without requiring the full app setup.
 * 
 * Usage:
 *   1. Install dependencies: composer install
 *   2. Configure config/email.php with your Gmail settings
 *   3. Update $testEmail below with your email address
 *   4. Run: php test_email.php
 */

declare(strict_types=1);

define('BASE_PATH', __DIR__);

// Load Composer autoloader
if (!file_exists(BASE_PATH . '/vendor/autoload.php')) {
    die("❌ ERROR: Composer dependencies not installed.\n\nPlease run: composer install\n\n");
}

require_once BASE_PATH . '/vendor/autoload.php';

// Autoload app classes
spl_autoload_register(function (string $class): void {
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ========================================
// CONFIGURE YOUR TEST EMAIL HERE
// ========================================
$testEmail = 'carletsrapmo@gmail.com'; // Change this to your email
$testName  = 'Test User';
// ========================================

echo "═══════════════════════════════════════════════════════════════\n";
echo "  EMAIL NOTIFICATION TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Check email config
$configPath = BASE_PATH . '/config/email.php';
if (!file_exists($configPath)) {
    die("❌ ERROR: Config file not found: {$configPath}\n\nPlease create it from config/email.example.php\n\n");
}

$emailConfig = require $configPath;

echo "📋 Configuration Check:\n";
echo "   SMTP Host: " . ($emailConfig['smtp_host'] ?? 'NOT SET') . "\n";
echo "   SMTP Port: " . ($emailConfig['smtp_port'] ?? 'NOT SET') . "\n";
echo "   Username:  " . ($emailConfig['smtp_username'] ?? 'NOT SET') . "\n";
echo "   From:      " . ($emailConfig['from_email'] ?? 'NOT SET') . "\n";
echo "   Enabled:   " . ($emailConfig['enabled'] ? '✅ YES' : '❌ NO (set to true in config)') . "\n\n";

if (empty($emailConfig['smtp_username']) || empty($emailConfig['smtp_password'])) {
    die("❌ ERROR: SMTP credentials not configured in config/email.php\n\n");
}

if (!$emailConfig['enabled']) {
    echo "⚠️  WARNING: Email is disabled in config. Set 'enabled' => true to send emails.\n";
    echo "   Continuing test anyway...\n\n";
}

echo "📧 Sending test email to: {$testEmail}\n\n";

try {
    $emailService = new Service\EmailService();
    
    // Build test notification
    $notification = [
        'type' => 'new_announcement',
        'target_type' => 'announcement',
        'target_id' => 1,
        'target_title' => 'Test Announcement: Email System is Working!',
        'actor_id' => 0,
    ];
    
    $actor = null; // System notification
    $siteUrl = $emailConfig['site_url'] ?? 'http://localhost/DigitalEvangelization';
    
    $content = $emailService->buildNotificationContent($notification, $actor, $siteUrl);
    
    $result = $emailService->sendNotificationEmail(
        $testEmail,
        $testName,
        $content['subject'],
        $content['html'],
        $content['text']
    );
    
    if ($result) {
        echo "✅ SUCCESS! Email sent successfully.\n\n";
        echo "📬 Check your inbox at: {$testEmail}\n";
        echo "   (It may take a few seconds to arrive)\n\n";
        echo "   Subject: {$content['subject']}\n\n";
    } else {
        echo "❌ FAILED: Email could not be sent.\n\n";
        echo "Common issues:\n";
        echo "   • Check your Gmail App Password (no spaces, 16 characters)\n";
        echo "   • Enable 2-Factor Authentication on your Google account first\n";
        echo "   • Verify smtp_username is your full Gmail address\n";
        echo "   • Check PHP error log for details\n\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  Test Complete\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
