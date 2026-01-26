<?php
// Script to verify the database and tables were created correctly

// Include the database connection
require_once 'includes/db_connection.php';

echo "Verifying Novatech database setup...\n";

try {
    // Check if database exists by connecting to it
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    echo "✓ Connected to database: " . $result['db_name'] . "\n";

    // List all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✓ Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }

    // Check specific tables
    $required_tables = ['users', 'products', 'investments', 'wallets', 'transactions', 'recharges', 'withdrawals', 'daily_earnings'];
    
    echo "\nVerifying required tables:\n";
    foreach ($required_tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' is missing\n";
        }
    }

    // Check admin user
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        echo "✓ Admin user exists\n";
    } else {
        echo "✗ Admin user is missing\n";
    }

    // Check sample products
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        echo "✓ Sample products added (" . $result['count'] . " products)\n";
    } else {
        echo "✗ No sample products found\n";
    }

    echo "\nDatabase verification completed successfully!\n";

} catch (PDOException $e) {
    echo "Verification failed: " . $e->getMessage() . "\n";
}

?>