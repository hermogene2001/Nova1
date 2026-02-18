<?php
require_once('../includes/db.php');

// Initialize variables
$message = '';
$user = null;

// Check if we're editing an existing user
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $query = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['id']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $balance = mysqli_real_escape_string($conn, $_POST['balance']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    // Basic validation
    if (!empty($phone_number) && !empty($role)) {
        $query = "UPDATE users SET phone_number = '$phone_number', balance = '$balance', role = '$role' WHERE id = $user_id";
        
        if (mysqli_query($conn, $query)) {
            header('Location: ../admin/users.php?message=User updated successfully');
            exit();
        } else {
            $message = "Error updating user: " . mysqli_error($conn);
        }
    } else {
        $message = "All fields are required!";
    }
}
?>
