<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once('../includes/db.php');

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="phone_numbers_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, ['User ID', 'Name', 'Phone Number', 'Role', 'Status', 'Balance', 'Created At']);

// Fetch all users with phone numbers
$query = "SELECT id, fname, phone_number, role, status, balance, created_at FROM users WHERE phone_number IS NOT NULL AND phone_number != '' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['id'],
            $row['fname'] ?? 'N/A',
            $row['phone_number'],
            ucfirst($row['role']),
            ucfirst($row['status'] ?? 'active'),
            '$' . number_format($row['balance'], 2),
            $row['created_at']
        ]);
    }
}

fclose($output);
mysqli_close($conn);
exit();
?>