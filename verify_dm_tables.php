<?php
/**
 * Quick verification script to check if Direct Message tables exist
 * Run this in your browser: http://localhost/DigitalEvangelization/verify_dm_tables.php
 */
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = getDB();
    
    echo "=== Checking Direct Message Tables ===\n\n";
    
    // Check conversations table
    $stmt = $db->query("SHOW TABLES LIKE 'direct_message_conversations'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table 'direct_message_conversations' exists\n";
        
        // Get table structure
        $stmt = $db->query("DESCRIBE direct_message_conversations");
        echo "  Columns:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "    - {$row['Field']} ({$row['Type']})\n";
        }
    } else {
        echo "✗ Table 'direct_message_conversations' DOES NOT exist\n";
        echo "  Run: database/add_direct_messages.sql\n";
    }
    
    echo "\n";
    
    // Check messages table
    $stmt = $db->query("SHOW TABLES LIKE 'direct_messages'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table 'direct_messages' exists\n";
        
        // Get table structure
        $stmt = $db->query("DESCRIBE direct_messages");
        echo "  Columns:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "    - {$row['Field']} ({$row['Type']})\n";
        }
        
        // Get count
        $stmt = $db->query("SELECT COUNT(*) as count FROM direct_messages");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  Total messages: {$count}\n";
    } else {
        echo "✗ Table 'direct_messages' DOES NOT exist\n";
        echo "  Run: database/add_direct_messages.sql\n";
    }
    
    echo "\n=== Verification Complete ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Make sure your database is running and config/database.php is set up correctly.\n";
}
