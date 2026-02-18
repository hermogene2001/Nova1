<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect to admin login if not admin
    header('Location: ../admin_login.php');
    exit();
}

// ini_set('display_errors', 2);
// ini_set('display_startup_errors', 2);
// error_reporting(E_ALL);

require_once('../includes/db.php');

// Get count of users
$user_count_query = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'admin'";
$user_count_result = mysqli_query($conn, $user_count_query);
$user_count = mysqli_fetch_assoc($user_count_result)['total_users'] ?? 0;

// Get count of products
$product_count_query = "SELECT COUNT(*) AS total_products FROM products";
$product_count_result = mysqli_query($conn, $product_count_query);
$product_count = mysqli_fetch_assoc($product_count_result)['total_products'] ?? 0;

// Get pending withdrawals
$pending_actions_query = "SELECT COUNT(*) AS pending_actions FROM withdrawals WHERE status = 'pending'";
$pending_actions_result = mysqli_query($conn, $pending_actions_query);
$pending_actions = mysqli_fetch_assoc($pending_actions_result)['pending_actions'] ?? 0;

// Get pending recharges
$pending_recharges_query = "SELECT COUNT(*) AS pending_recharges FROM recharges WHERE status = 'pending'";
$pending_recharges_result = mysqli_query($conn, $pending_recharges_query);
$pending_recharges = mysqli_fetch_assoc($pending_recharges_result)['pending_recharges'] ?? 0;

// Get count of transactions
$transaction_count_query = "SELECT COUNT(*) AS total_transactions FROM transactions";
$transaction_count_result = mysqli_query($conn, $transaction_count_query);
$total_transactions = mysqli_fetch_assoc($transaction_count_result)['total_transactions'] ?? 0;

// Fetch social media links from the database
$social_links_query = "SELECT * FROM social_links WHERE id = 1";
$social_links_result = mysqli_query($conn, $social_links_query);
$social_links = mysqli_fetch_assoc($social_links_result) ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycling Paper Arts Magazine - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-main: #FFFFFF;
            --text-main: #1A1A1A;
            --text-muted: #6C757D;
            --primary: #0D6EFD;
            --secondary: #DC3545;
            --section-bg: #F8F9FA;
        }

        body {
            background: linear-gradient(135deg, var(--section-bg) 0%, var(--bg-main) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .magazine-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--text-main) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .magazine-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M10 20 Q50 10 90 20 Q50 30 10 20" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 200px 50px;
            animation: wave 8s linear infinite;
        }

        @keyframes wave {
            0% { transform: translateX(-200px); }
            100% { transform: translateX(200px); }
        }

        .magazine-logo {
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }

        .magazine-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .metric-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            margin-bottom: 2rem;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .metric-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--text-main);
            margin: 0.5rem 0;
        }

        .metric-label {
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .magazine-btn {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            border: none;
            color: var(--bg-main);
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .magazine-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }

        .action-btn {
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transition: left 0.3s ease;
            z-index: 1;
        }

        .action-btn:hover::before {
            left: 0;
        }

        .action-btn:hover {
            color: white;
            border-color: var(--secondary);
        }

        .action-btn i,
        .action-btn span {
            position: relative;
            z-index: 2;
        }

        .magazine-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            opacity: 0.03;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="20" height="10" x="10" y="45" fill="currentColor"/><rect width="15" height="8" x="35" y="46" fill="currentColor"/><rect width="25" height="12" x="55" y="44" fill="currentColor"/></svg>');
            background-size: 200px 100px;
            animation: ship-move 20s linear infinite;
        }

        @keyframes ship-move {
            0% { transform: translateX(-100px); }
            100% { transform: translateX(100px); }
        }

        .real-time-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .container-fluid {
            max-width: 1400px;
        }

        .row-equal-height {
            display: flex;
            flex-wrap: wrap;
        }

        .row-equal-height > [class*='col-'] {
            display: flex;
        }

        .metric-card {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .magazine-logo {
                font-size: 2rem;
            }
            
            .metric-number {
                font-size: 2rem;
            }
            
            .card-icon {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="magazine-pattern"></div>
    
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
                        <a class="nav-link" href="phone_management.php"><i class="fas fa-phone me-1"></i>Phone Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="support_phone.php"><i class="fas fa-headset me-1"></i>Support Phone</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php"><i class="fas fa-box me-1"></i>Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php"><i class="fas fa-exchange-alt me-1"></i>Transactions</a>
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
    
    <!-- Header Section -->
    <div class="magazine-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col text-center">
                    <div class="magazine-logo">
                        <i class="fas fa-newspaper me-3"></i>
                        RECYCLING PAPER ARTS MAGAZINE
                    </div>
                    <div class="magazine-subtitle">Creative Publishing Solutions - Admin Dashboard</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Real Time Section -->
        <div class="real-time-section">
            <?php include'../includes/Real_Time.php' ?>
            <a href="rates.php" class="btn btn-danger"><i class="fas fa-money-bill"></i>&nbsp;Manage Exchange Rates
</a>
        </div>

        <!-- Key Metrics Section -->
        <div class="row row-equal-height">
            <!-- Total Users Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="metric-card">
                    <div class="card-body text-center">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="metric-label">Total Users</div>
                        <div class="metric-number"><?= $user_count; ?></div>
                        <a href="users.php" class="magazine-btn">
                            <i class="fas fa-eye me-2"></i>View All Users
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Total Products Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="metric-card">
                    <div class="card-body text-center">
                        <div class="card-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="metric-label">Total Products</div>
                        <div class="metric-number"><?= $product_count; ?></div>
                        <a href="products.php" class="magazine-btn">
                            <i class="fas fa-eye me-2"></i>View All Products
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pending Withdrawals Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="metric-card">
                    <div class="card-body text-center">
                        <div class="card-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="metric-label">Pending Withdrawals</div>
                        <div class="metric-number"><?= $pending_actions; ?></div>
                        <a href="pending_withdrawals.php" class="magazine-btn">
                            <i class="fas fa-eye me-2"></i>View Withdrawals
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pending Recharges Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="metric-card">
                    <div class="card-body text-center">
                        <div class="card-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="metric-label">Pending Recharges</div>
                        <div class="metric-number"><?= $pending_recharges; ?></div>
                        <a href="pending_recharges.php" class="magazine-btn">
                            <i class="fas fa-eye me-2"></i>View Recharges
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Card -->
        <div class="row justify-content-center">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="metric-card">
                    <div class="card-body text-center">
                        <div class="card-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="metric-label">Total Transactions</div>
                        <div class="metric-number"><?= $total_transactions; ?></div>
                        <a href="transactions.php" class="magazine-btn">
                            <i class="fas fa-eye me-2"></i>View All Transactions
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons Section -->
        <div class="row justify-content-center mt-5">
            <div class="col-lg-4 col-md-6 mb-3">
                <button class="action-btn w-100" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="fas fa-key me-2"></i>
                    <span>Change Password</span>
                </button>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <button class="action-btn w-100" data-bs-toggle="modal" data-bs-target="#createAgentModal">
                    <i class="fas fa-user-plus me-2"></i>
                    <span>Create New Agent</span>
                </button>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <button class="action-btn w-100" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-box-open me-2"></i>
                    <span>Add New Product</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php include('modals.php'); // Import modals ?>
</body>
</html>