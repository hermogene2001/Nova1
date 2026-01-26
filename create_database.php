<?php
// Script to create the novatech_db database

// Database configuration for initial connection (without specifying dbname)
$host = 'localhost';
$username = 'root'; // Replace with your database username
$password = '';    // Replace with your database password

try {
    // Create a new PDO instance without specifying dbname
    $pdo = new PDO("mysql:host=$host", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS novatech_db");
    echo "Database 'novatech_db' created successfully!\n";

    // Now connect to the newly created database
    $pdo = new PDO("mysql:host=$host;dbname=novatech_db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read and execute the SQL file
    $sql = file_get_contents('database_setup.sql');
    
    // Split the SQL file into individual statements
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                // Only show success messages for CREATE, INSERT, and CREATE INDEX statements
                if (stripos($statement, 'CREATE TABLE') === 0 || 
                    stripos($statement, 'INSERT') === 0 || 
                    stripos($statement, 'CREATE INDEX') === 0) {
                    echo "Executed: " . substr($statement, 0, 50) . "...\n";
                }
            } catch (Exception $e) {
                echo "Error executing statement: " . $e->getMessage() . "\n";
                echo "Statement: " . $statement . "\n";
            }
        }
    }
    
    echo "\nDatabase setup completed successfully!\n";
    echo "An admin user has been created with:\n";
    echo "Phone: 0780000000\n";
    echo "Password: password (you should change this immediately)\n";
    echo "Sample products have been added to get you started.\n";

} catch (PDOException $e) {
    // Handle connection errors
    echo "Database setup failed: " . $e->getMessage() . "\n";
}

echo "\nSetup process finished.\n";
?>