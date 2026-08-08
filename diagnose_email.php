<?php
/**
 * Email Configuration Diagnostic Script
 * Checks all requirements for email notifications
 */

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  EMAIL NOTIFICATION DIAGNOSTIC                             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// 1. Check PHP version
echo "1. PHP Version: " . PHP_VERSION;
echo (version_compare(PHP_VERSION, '8.1.0', '>=') ? " ✅\n" : " ❌ Need 8.1+\n");

// 2. Check OpenSSL
echo "2. OpenSSL Extension: ";
echo (extension_loaded('openssl') ? "✅ Loaded\n" : "❌ NOT LOADED - Enable in php.ini\n");

// 3. Check Composer autoloader
echo "3. Composer Dependencies: ";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Installed\n";
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    echo "❌ NOT INSTALLED - Run: composer install\n";
    die("\n❌ CRITICAL: Run 'composer install' first!\n\n");
}

// 4. Check PHPMailer
echo "4. PHPMailer Library: ";
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "✅ Found\n";
} else {
    echo "❌ NOT FOUND\n";
    die("\n❌ CRITICAL: PHPMailer not found. Run: composer install\n\n");
}

// 5. Check email config
echo "5. Email Configuration: ";
if (file_exists(__DIR__ . '/config/email.php')) {
    $config = require __DIR__ . '/config/email.php';
    echo "✅ Found\n";
    
    echo "   - Enabled: " . ($config['enabled'] ? '✅ YES' : '❌ NO - Set to true') . "\n";
    echo "   - Username: " . (empty($config['smtp_username']) ? '❌ NOT SET' : '✅ ' . $config['smtp_username']) . "\n";
    echo "   - Password: " . (empty($config['smtp_password']) ? '❌ NOT SET' : '✅ Set (' . strlen($config['smtp_password']) . ' chars)') . "\n";
    echo "   - From Email: " . (empty($config['from_email']) ? '❌ NOT SET' : '✅ ' . $config['from_email']) . "\n";
    
    if (strlen($config['smtp_password']) !== 16) {
        echo "   ⚠️  WARNING: App Password should be exactly 16 characters (yours is " . strlen($config['smtp_password']) . ")\n";
    }
} else {
    echo "❌ NOT FOUND\n";
    die("\n❌ CRITICAL: Create config/email.php from config/email.example.php\n\n");
}

// 6. Test SMTP connection
echo "\n6. Testing SMTP Connection...\n";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];
    $mail->Port = $config['smtp_port'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_username'];
    $mail->Password = $config['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Timeout = 10;
    
    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
    
    echo "   Connecting to " . $config['smtp_host'] . ":" . $config['smtp_port'] . "...\n\n";
    
    if ($mail->smtpConnect()) {
        echo "\n   ✅ SMTP Connection Successful!\n";
        $mail->smtpClose();
    } else {
        echo "\n   ❌ SMTP Connection Failed\n";
    }
} catch (Exception $e) {
    echo "\n   ❌ Error: " . $e->getMessage() . "\n";
    echo "\n   Common fixes:\n";
    echo "   - Verify your Gmail App Password is correct\n";
    echo "   - Make sure 2-Factor Auth is enabled on your Google account\n";
    echo "   - Check your internet connection\n";
    echo "   - Visit: https://myaccount.google.com/apppasswords\n";
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  DIAGNOSTIC COMPLETE                                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

if (extension_loaded('openssl') && 
    file_exists(__DIR__ . '/vendor/autoload.php') && 
    !empty($config['smtp_username']) && 
    !empty($config['smtp_password']) && 
    $config['enabled']) {
    echo "✅ All checks passed! Try running: php test_email.php\n\n";
} else {
    echo "⚠️  Fix the issues above and try again.\n\n";
}
