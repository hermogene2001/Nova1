<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 2);
ini_set('display_startup_errors', 2);
error_reporting(E_ALL);

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

require_once('../includes/db.php');

// Check which user reference column exists in withdrawals table
$userColumn = 'client_id';
$checkResult = mysqli_query($conn, "SHOW COLUMNS FROM withdrawals LIKE 'client_id'");
if (!$checkResult || mysqli_num_rows($checkResult) == 0) {
    $checkResult = mysqli_query($conn, "SHOW COLUMNS FROM withdrawals LIKE 'user_id'");
    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        $userColumn = 'user_id';
    } else {
        $checkResult = mysqli_query($conn, "SHOW COLUMNS FROM withdrawals LIKE 'client_phone_number'");
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $userColumn = 'client_phone_number';
        } else {
            // If none of the expected columns exist, default to 'client_id' as fallback
            $userColumn = 'client_id';
        }
    }
}

// Check which amount column exists in withdrawals table
$amountColumn = 'amount_rwf';
$checkResult = mysqli_query($conn, "SHOW COLUMNS FROM withdrawals LIKE 'amount_rwf'");
if (!$checkResult || mysqli_num_rows($checkResult) == 0) {
    $checkResult = mysqli_query($conn, "SHOW COLUMNS FROM withdrawals LIKE 'amount_usd'");
    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        $amountColumn = 'amount_usd';
    } else {
        $checkResult = mysqli_query($conn, "SHOW COLUMNS FROM withdrawals LIKE 'amount'");
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $amountColumn = 'amount';
        } else {
            // If none of the expected amount columns exist, default to 'amount_rwf' as fallback
            $amountColumn = 'amount_rwf';
        }
    }
}

// Fetch pending withdrawals with phone number from the users table
$pending_withdrawals_query = "
    SELECT withdrawals.id, withdrawals." . $amountColumn . ", withdrawals.date, users.phone_number 
    FROM withdrawals 
    JOIN users ON withdrawals." . $userColumn . " = users.id 
    WHERE withdrawals.status = 'pending'";
$pending_withdrawals_result = mysqli_query($conn, $pending_withdrawals_query);
$pending_count = mysqli_num_rows($pending_withdrawals_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Withdrawals - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .withdrawals-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 10px;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #dc3545;
        }
        .stats-label {
            color: #6c757d;
            font-weight: 500;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-newspaper me-2"></i>Admin Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php"><i class="fas fa-users me-1"></i>Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php"><i class="fas fa-box me-1"></i>Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php"><i class="fas fa-exchange-alt me-1"></i>Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="pending_withdrawals.php"><i class="fas fa-money-bill-wave me-1"></i>Withdrawals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pending_recharges.php"><i class="fas fa-credit-card me-1"></i>Recharges</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="update_social_links.php"><i class="fas fa-hashtag me-1"></i>SocialMedia</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-light" href="../actions/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <!-- Header Section -->
        <div class="withdrawals-header text-center">
            <h1><i class="fas fa-money-bill-wave me-3"></i>Pending Withdrawals</h1>
            <p class="mb-0">Manage and process pending withdrawal requests</p>
        </div>
        
        <!-- Stats Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="stats-card">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="stats-number"><?= $pending_count ?></div>
                            <div class="stats-label">Pending Requests</div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-number">0</div>
                            <div class="stats-label">Total Amount</div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-number">0</div>
                            <div class="stats-label">Today's Requests</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <h2 class="mb-4">Withdrawal Requests</h2>
            
            <?php if ($pending_count > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>User Phone Number</th>
                                <th>Amount</th>
                                <th>Date Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($withdrawal = mysqli_fetch_assoc($pending_withdrawals_result)) { ?>
                                <tr>
                                    <td><?= $withdrawal['id']; ?></td>
                                    <td><?= $withdrawal['phone_number']; ?></td>
                                    <td><?= number_format($withdrawal[$amountColumn], 2); ?> RWF</td>
                                    <td><?= $withdrawal['date']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-success me-1">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>No Pending Withdrawals</h3>
                    <p>There are currently no pending withdrawal requests.</p>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
mysqli_close($conn);
?>