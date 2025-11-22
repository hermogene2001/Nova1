<?php
// Database setup script for Novatech Financial Management System

// Include the database connection
require_once 'includes/db_connection.php';

echo "Starting database setup for Novatech...\n";

try {
    // Read the SQL file
    $sql = file_get_contents('database_setup.sql');
    
    // Execute the SQL commands
    if ($pdo->exec($sql) !== false) {
        echo "Database and tables created successfully!\n";
        
        // Verify tables were created
        $tables = [
            'users', 
            'products', 
            'investments', 
            'wallets', 
            'transactions', 
            'recharges', 
            'withdrawals', 
            'daily_earnings'
        ];
        
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->rowCount() > 0) {
                echo "✓ Table '$table' exists\n";
            } else {
                echo "✗ Table '$table' was not created\n";
            }
        }
        
        echo "\nDatabase setup completed successfully!\n";
        echo "An admin user has been created with:\n";
        echo "Phone: 0780000000\n";
        echo "Password: password (you should change this immediately)\n";
        echo "Sample products have been added to get you started.\n";
        
    } else {
        echo "Error executing SQL commands.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nSetup process finished.\n";
?>