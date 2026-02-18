<?php
require_once('../includes/db.php');

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Generate a default password
    $defaultPassword = 'password123'; // Default password
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $id);
    
    if ($stmt->execute()) {
        // Log the password reset activity
        $admin_id = $_SESSION['user_id'] ?? 0;
        $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, description, timestamp) VALUES (?, 'PASSWORD_RESET', 'Admin reset password for user ID: {$id}', NOW())");
        $log_stmt->bind_param("i", $admin_id);
        $log_stmt->execute();
        $log_stmt->close();
        
        header("Location: ../admin/users.php?success=Password reset successfully. New password is: {$defaultPassword}");
    } else {
        header("Location: ../admin/users.php?error=Error resetting password");
    }
    
    $stmt->close();
} else {
    header("Location: ../admin/users.php?error=Invalid request");
}

$conn->close();
?>