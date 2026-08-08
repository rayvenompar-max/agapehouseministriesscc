<?php
/**
 * Test announcement notification creation
 * Simulates what happens when an admin creates an announcement
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/vendor/autoload.php';

// Autoload app classes
spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use Repository\NotificationRepository;
use Repository\MemberRepository;

echo "═══════════════════════════════════════════════════════════════\n";
echo "  TEST ANNOUNCEMENT NOTIFICATION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$db = getDB();
$notificationRepo = new NotificationRepository($db);
$memberRepo = new MemberRepository($db);

// Get all active members
$memberIds = $memberRepo->getAllActiveMemberIds();
echo "Found " . count($memberIds) . " active member(s)\n\n";

if (empty($memberIds)) {
    echo "❌ No active members found. Cannot send notifications.\n\n";
    exit;
}

// Simulate creating a broadcast notification for all members
echo "Simulating announcement notification...\n\n";

$testAnnouncementId = 999;
$testTitle = "TEST: Email Notification System Check";

foreach ($memberIds as $memberId) {
    echo "Creating notification for Member ID: {$memberId}...\n";
    
    try {
        $notificationRepo->createBroadcast(
            recipientId:  $memberId,
            type:         'new_announcement',
            targetType:   'announcement',
            targetId:     $testAnnouncementId,
            targetTitle:  $testTitle
        );
        echo "  ✅ Notification created\n";
        echo "  📧 Email should be sent to member's email\n";
    } catch (Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  TEST COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Check your email inbox (and spam folder)\n";
echo "✅ Check the notifications table in the database\n";
echo "✅ Check PHP error log for any errors\n\n";

echo "To check notifications in database:\n";
echo "SELECT * FROM notifications WHERE target_id = {$testAnnouncementId};\n\n";
