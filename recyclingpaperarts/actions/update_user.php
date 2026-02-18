<?php
// File: actions/update_user.php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and validate form data
        $user_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
        $role = isset($_POST['role']) ? trim($_POST['role']) : '';
        $balance = isset($_POST['balance']) ? floatval($_POST['balance']) : 0;
        $status = isset($_POST['status']) ? trim($_POST['status']) : 'active';
        
        // Validate required fields
        if ($user_id <= 0) {
            throw new Exception('Invalid user ID');
        }
        
        if (empty($phone_number)) {
            throw new Exception('Phone number is required');
        }
        
        if (!in_array($role, ['client', 'agent'])) {
            throw new Exception('Invalid role selected');
        }
        
        if ($balance < 0) {
            throw new Exception('Balance cannot be negative');
        }
        
        if (!in_array($status, ['active', 'inactive', 'suspended'])) {
            throw new Exception('Invalid status selected');
        }
        
        // Validate phone number format (basic validation)
        if (!preg_match('/^[\d\+\-\(\)\s]+$/', $phone_number)) {
            throw new Exception('Invalid phone number format');
        }
        
        // Check if phone number already exists for another user
        $check_phone_sql = "SELECT id FROM users WHERE phone_number = ? AND id != ?";
        $check_stmt = $conn->prepare($check_phone_sql);
        $check_stmt->bind_param("si", $phone_number, $user_id);
        $check_stmt->execute();
        $phone_result = $check_stmt->get_result();
        
        if ($phone_result->num_rows > 0) {
            throw new Exception('Phone number already exists for another user');
        }
        
        // Check if user exists and is not admin
        $user_check_sql = "SELECT role FROM users WHERE id = ? AND role != 'admin'";
        $user_stmt = $conn->prepare($user_check_sql);
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_result->num_rows === 0) {
            throw new Exception('User not found or cannot be modified');
        }
        
        // Start transaction
        $conn->autocommit(false); // Turn off auto-commit
        
        // Update user information
        $update_sql = "UPDATE users SET 
                       phone_number = ?, 
                       role = ?, 
                       balance = ?, 
                       status = ?,
                       updated_at = NOW() 
                       WHERE id = ? AND role != 'admin'";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssdsi", $phone_number, $role, $balance, $status, $user_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception('Failed to update user information');
        }
        
        if ($update_stmt->affected_rows === 0) {
            throw new Exception('No changes were made or user not found');
        }
        
        // Log the admin action (optional - create admin_logs table if needed)
        $log_sql = "INSERT INTO admin_logs (admin_id, action, target_user_id, details, created_at) 
                    VALUES (?, 'update_user', ?, ?, NOW())";
        
        // Check if admin_logs table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'admin_logs'");
        if ($table_check->num_rows > 0) {
            $details = json_encode([
                'phone_number' => $phone_number,
                'role' => $role,
                'balance' => $balance,
                'status' => $status
            ]);
            
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("iis", $_SESSION['user_id'], $user_id, $details);
            if (!$log_stmt->execute()) {
                throw new Exception('Failed to log admin action');
            }
        }
        
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true); // Turn auto-commit back on
        
        // Clean up statements
        $check_stmt->close();
        $user_stmt->close();
        $update_stmt->close();
        if (isset($log_stmt)) {
            $log_stmt->close();
        }
        
        // Success response
        $_SESSION['success_message'] = 'User updated successfully!';
        header('Location: ../admin/users.php');
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $conn->autocommit(true); // Ensure auto-commit is back on
        
        // Error response
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        header('Location: ../admin/users.php');
        exit();
    }
} else {
    // Invalid request method
    header('Location: ../admin/users.php');
    exit();
}

$conn->close();
?>