<?php
// Simple script to test database connection

require_once 'includes/db_connection.php';

echo "Testing database connection...\n";

try {
    // Test PDO connection
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✓ PDO connection successful\n";
    } else {
        echo "✗ PDO connection failed\n";
    }
    
    // Test mysqli connection
    $result = mysqli_query($conn, "SELECT 1 as test");
    if ($result) {
        echo "✓ MySQLi connection successful\n";
    } else {
        echo "✗ MySQLi connection failed\n";
    }
    
    echo "Database connection test completed successfully!\n";

} catch (Exception $e) {
    echo "Connection test failed: " . $e->getMessage() . "\n";
}

?>