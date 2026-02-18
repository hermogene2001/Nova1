<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

require_once('../includes/db.php');

// Default query (if no search term is provided)
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Modify the SQL query to include the search parameter
$transactions_query = "
    SELECT t.*, u.phone_number 
    FROM transactions t 
    JOIN users u ON t.client_id = u.id 
    WHERE u.phone_number LIKE ? OR t.transaction_type LIKE ?
    ORDER BY t.transaction_type, t.date DESC
";

// Prepare the statement to prevent SQL injection
$stmt = $conn->prepare($transactions_query);

// Bind the search parameter
$search = '%' . $search_query . '%';
$stmt->bind_param('ss', $search, $search); // Both phone_number and transaction_type are string types
$stmt->execute();
$transactions_result = $stmt->get_result();

$current_type = null; // Variable to track the current transaction type
$total_transactions = mysqli_num_rows($transactions_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .transactions-header {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
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
            color: #0d6efd;
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
                        <a class="nav-link active" href="transactions.php"><i class="fas fa-exchange-alt me-1"></i>Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pending_withdrawals.php"><i class="fas fa-money-bill-wave me-1"></i>Withdrawals</a>
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
        <div class="transactions-header text-center">
            <h1><i class="fas fa-exchange-alt me-3"></i>Transactions Management</h1>
            <p class="mb-0">Monitor all financial transactions and user activities</p>
        </div>
        
        <!-- Stats Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="stats-card">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stats-number"><?= $total_transactions ?></div>
                            <div class="stats-label">Total Transactions</div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-number">0</div>
                            <div class="stats-label">Total Amount</div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-number">0</div>
                            <div class="stats-label">Recharges</div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-number">0</div>
                            <div class="stats-label">Withdrawals</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <h2 class="mb-4">All Transactions</h2>
            
            <!-- Search Form -->
            <form id="searchForm" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" id="searchInput" class="form-control" value="<?= htmlspecialchars($search_query); ?>" placeholder="Search by phone number or transaction type">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
            
            <?php if ($total_transactions > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>User Phone</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsTable">
                            <?php 
                            mysqli_data_seek($transactions_result, 0); // Reset result pointer
                            while ($transaction = mysqli_fetch_assoc($transactions_result)) { 
                                if ($current_type !== $transaction['transaction_type']) {
                                    // Display a header row for each new transaction type
                                    $current_type = $transaction['transaction_type'];
                            ?>
                                <tr class="table-primary">
                                    <td colspan="5"><strong><?= ucfirst($current_type); ?> Transactions</strong></td>
                                </tr>
                            <?php } ?>
                                <tr>
                                    <td><?= $transaction['id']; ?></td>
                                    <td><?= $transaction['phone_number']; ?></td>
                                    <td><?= $transaction['amount']; ?></td>
                                    <td><?= ucfirst($transaction['transaction_type']); ?></td>
                                    <td><?= $transaction['date']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-exchange-alt"></i>
                    <h3>No Transactions Found</h3>
                    <p>There are currently no transactions in the system.</p>
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
$stmt->close();
$conn->close();
?>