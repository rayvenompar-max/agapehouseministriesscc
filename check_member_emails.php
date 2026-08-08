<?php
/**
 * Check if members have email addresses in the database
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();

echo "═══════════════════════════════════════════════════════════════\n";
echo "  CHECKING MEMBER EMAIL ADDRESSES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get all members
$stmt = $db->query("SELECT id, display_name, username, email, created_at FROM members ORDER BY id");
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($members)) {
    echo "❌ No members found in database.\n\n";
    exit;
}

echo "Found " . count($members) . " member(s):\n\n";

foreach ($members as $member) {
    echo "Member ID: {$member['id']}\n";
    echo "  Name: {$member['display_name']} (@{$member['username']})\n";
    echo "  Email: " . ($member['email'] ? "✅ {$member['email']}" : "❌ NO EMAIL") . "\n";
    echo "  Registered: {$member['created_at']}\n";
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n\n";

// Check if email addresses exist
$withEmail = array_filter($members, fn($m) => !empty($m['email']));
$withoutEmail = array_filter($members, fn($m) => empty($m['email']));

echo "Summary:\n";
echo "  ✅ Members with email: " . count($withEmail) . "\n";
echo "  ❌ Members without email: " . count($withoutEmail) . "\n\n";

if (count($withoutEmail) > 0) {
    echo "⚠️  WARNING: Some members don't have email addresses.\n";
    echo "   They will NOT receive email notifications!\n\n";
}
