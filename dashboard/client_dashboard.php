<?php
session_start();

// Check if the user is logged in and has the 'client' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'client') {
    header("Location: ../index.php"); // Redirect to login page if not logged in or not a client
    exit();
}

// Include your database connection
include('../includes/db_connection.php');

// Fetch user information from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch wallet information (balance is stored in wallets table)
$walletSql = "SELECT balance FROM wallets WHERE user_id = ?";
$walletStmt = $conn->prepare($walletSql);
$walletStmt->bind_param("i", $user_id);
$walletStmt->execute();
$walletResult = $walletStmt->get_result();
$wallet = $walletResult->fetch_assoc();
$walletStmt->close();

// Close the database connection

?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Theme CSS -->
    <link href="../assets/css/theme.css" rel="stylesheet">
</head>
<body>

    <!-- Include the navigation bar -->
    <?php include('nav.php'); ?>

    <div class="container">
        <!-- Dashboard Content -->
        <?php
        // Check if notifications table exists and handle gracefully
        try {
            $notificationQuery = "SELECT id, message, created_at FROM notifications WHERE user_id = ? AND is_read = 0";
            $stmt = $conn->prepare($notificationQuery);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($notification = $result->fetch_assoc()) {
                echo "<div class='notification'>";
                echo "<p>" . htmlspecialchars($notification['message']) . "</p>";
                echo "<small>Received on: " . $notification['created_at'] . "</small>";
                echo "</div>";
            }

            // Mark notifications as read (optional)
            $markReadQuery = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
            $markReadStmt = $conn->prepare($markReadQuery);
            $markReadStmt->bind_param("i", $_SESSION['user_id']);
            $markReadStmt->execute();
        } catch (Exception $e) {
            // Silently ignore if notifications table doesn't exist
            // This prevents the fatal error while allowing the rest of the page to load
        }
        ?>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Welcome, <?php echo $user['first_name']; ?>!</h3>
                    </div>
                    <div class="card-body">
                        <h5>Account Details</h5>
                        <p><strong>Phone Number:</strong> <?php echo $user['phone_number']; ?></p>
                        <p><strong>Balance:</strong> <?php echo number_format($wallet['balance'] ?? 0, 2)." "; ?><b>RWF</b></p>
                        <p><strong>Referral Code:</strong> <?php echo $user['referral_code']; ?></p>
                        <p><strong>Invitation Code:</strong> <?php echo $user['referred_by'] ?? 'N/A'; ?></p>
                    </div>
                </div>

                <!-- Quick Access Links -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Quick Access</h4>
                    </div>
                    <div class="card-body">
                        <a href="view_investments" class="btn btn-primary">View My Investments</a>
                        <a href="profile" class="btn btn-secondary">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Novatech. All Rights Reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Theme JS -->
    <script src="../assets/js/theme.js"></script>
</body>
</html>