<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once('../includes/db.php');

// Get POST data
$user_ids = $_POST['user_ids'] ?? [];

if (empty($user_ids) || !is_array($user_ids)) {
    echo json_encode(['success' => false, 'message' => 'No users selected']);
    exit();
}

$verified_count = 0;
$errors = [];

foreach ($user_ids as $user_id) {
    $user_id = intval($user_id);
    
    // Validate phone number format
    $query = "SELECT phone_number FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $phone = $row['phone_number'];
        
        // Basic phone number validation
        if (!empty($phone) && preg_match('/^[0-9]{10,15}$/', $phone)) {
            // Mark as verified (you can add a verified column to users table)
            // For now, we'll just count valid phones
            $verified_count++;
        } else {
            $errors[] = "Invalid phone format for user ID: $user_id";
        }
    } else {
        $errors[] = "User not found: $user_id";
    }
    
    $stmt->close();
}

// Return response
echo json_encode([
    'success' => true,
    'verified' => $verified_count,
    'total' => count($user_ids),
    'errors' => $errors
]);

$conn->close();
?>