<?php
/**
 * Gallery Support Migration Script
 * Run this once to add 'gallery' support to comments and post_likes tables
 * 
 * Access via: http://localhost/DigitalEvangelization/fix_gallery_migration.php
 */

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════════\n";
echo "  GALLERY SUPPORT MIGRATION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    $db = getDB();
    
    // Check current state of comments table
    echo "📋 Checking current comments table structure...\n";
    $stmt = $db->query("SHOW COLUMNS FROM comments LIKE 'target_type'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Current comments.target_type: {$column['Type']}\n\n";
    
    // Check current state of post_likes table
    echo "📋 Checking current post_likes table structure...\n";
    $stmt = $db->query("SHOW COLUMNS FROM post_likes LIKE 'target_type'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Current post_likes.target_type: {$column['Type']}\n\n";
    
    // Check if gallery is already in comments
    if (strpos($column['Type'], 'gallery') !== false) {
        echo "✅ Gallery support already exists in both tables!\n\n";
        
        // Show counts
        $stmt = $db->query("SELECT COUNT(*) as count FROM comments WHERE target_type = 'gallery'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   Gallery comments in database: {$count['count']}\n";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM post_likes WHERE target_type = 'gallery'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   Gallery likes in database: {$count['count']}\n\n";
        
        echo "✅ No migration needed. Gallery support is working!\n";
        exit;
    }
    
    echo "⚠️  Gallery support NOT found. Running migration...\n\n";
    
    // Add gallery to comments table
    echo "🔧 Adding 'gallery' to comments table...\n";
    $db->exec("ALTER TABLE comments MODIFY COLUMN target_type ENUM('media','article','announcement','gallery') NOT NULL");
    echo "   ✅ Comments table updated!\n\n";
    
    // Add gallery to post_likes table
    echo "🔧 Adding 'gallery' to post_likes table...\n";
    $db->exec("ALTER TABLE post_likes MODIFY COLUMN target_type ENUM('article','media','announcement','gallery') NOT NULL");
    echo "   ✅ Post_likes table updated!\n\n";
    
    // Verify the changes
    echo "🔍 Verifying changes...\n";
    $stmt = $db->query("SHOW COLUMNS FROM comments LIKE 'target_type'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   New comments.target_type: {$column['Type']}\n";
    
    $stmt = $db->query("SHOW COLUMNS FROM post_likes LIKE 'target_type'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   New post_likes.target_type: {$column['Type']}\n\n";
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✅ MIGRATION COMPLETE!\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    echo "You can now:\n";
    echo "  ✓ Comment on gallery posts\n";
    echo "  ✓ Like gallery posts\n";
    echo "  ✓ Receive notifications for gallery interactions\n\n";
    echo "⚠️  IMPORTANT: Delete this file (fix_gallery_migration.php) after running!\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Please make sure:\n";
    echo "  1. Your database is running\n";
    echo "  2. Database credentials are correct in config/database.php\n";
    echo "  3. You have ALTER TABLE permissions\n\n";
}
