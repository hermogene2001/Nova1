<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once('../includes/db.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_rate'])) {
        // Add new exchange rate
        $base_currency = $_POST['base_currency'];
        $target_currency = $_POST['target_currency'];
        $rate = $_POST['rate'];
        $effective_date = $_POST['effective_date'];
        
        $query = "INSERT INTO exchange_rates (base_currency, target_currency, rate, effective_date) 
                  VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssds", $base_currency, $target_currency, $rate, $effective_date);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        $_SESSION['message'] = "Exchange rate added successfully!";
        header("Location: exchange_rates.php");
        exit;
        
    } elseif (isset($_POST['update_rate'])) {
        // Update existing exchange rate
        $id = $_POST['id'];
        $rate = $_POST['rate'];
        $effective_date = $_POST['effective_date'];
        
        $query = "UPDATE exchange_rates SET rate = ?, effective_date = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "dsi", $rate, $effective_date, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        $_SESSION['message'] = "Exchange rate updated successfully!";
        header("Location: exchange_rates.php");
        exit;
        
    } elseif (isset($_GET['delete'])) {
        // Delete exchange rate
        $id = $_GET['delete'];
        
        $query = "DELETE FROM exchange_rates WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        $_SESSION['message'] = "Exchange rate deleted successfully!";
        header("Location: exchange_rates.php");
        exit;
    }
}

// Get all exchange rates
$query = "SELECT * FROM exchange_rates ORDER BY effective_date DESC, base_currency, target_currency";
$result = mysqli_query($conn, $query);
$exchange_rates = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Exchange Rates | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --admin-primary: #2c3e50;
            --admin-secondary: #34495e;
            --admin-accent: #3498db;
            --admin-light: #ecf0f1;
            --admin-dark: #2c3e50;
        }

        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-header {
            background-color: var(--admin-primary);
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .admin-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }

        .btn-admin {
            background-color: var(--admin-accent);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .btn-admin:hover {
            background-color: #2980b9;
            color: white;
            transform: translateY(-2px);
        }

        .btn-danger {
            padding: 8px 20px;
            border-radius: 4px;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .table th {
            background-color: var(--admin-secondary);
            color: white;
        }

        .table td, .table th {
            vertical-align: middle;
        }

        .action-btns .btn {
            margin-right: 5px;
        }

        .modal-header {
            background-color: var(--admin-primary);
            color: white;
        }

        .nav-sidebar {
            background-color: var(--admin-secondary);
            min-height: 100vh;
            padding-top: 20px;
        }

        .nav-sidebar .nav-link {
            color: var(--admin-light);
            margin-bottom: 5px;
            border-radius: 4px;
            padding: 10px 15px;
        }

        .nav-sidebar .nav-link:hover, .nav-sidebar .nav-link.active {
            background-color: var(--admin-accent);
            color: white;
        }

        .nav-sidebar .nav-link i {
            margin-right: 10px;
        }

        .content-wrapper {
            padding: 20px;
        }

        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1000;
            background: var(--admin-accent);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 20px;
        }

        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
            
            .nav-sidebar {
                position: fixed;
                left: -250px;
                top: 0;
                z-index: 999;
                width: 250px;
                transition: left 0.3s;
            }
            
            .nav-sidebar.show {
                left: 0;
            }
            
            .content-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="nav-sidebar" id="navSidebar">
            <div class="text-center mb-4">
                <h4 class="text-white">Admin Panel</h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="exchange_rates.php">
                        <i class="fas fa-exchange-alt"></i> Exchange Rates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="transactions.php">
                        <i class="fas fa-money-bill-wave"></i> Transactions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="update_social_links.php">
                        <i class="fas fa-hashtag"></i> SocialMedia
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link" href="../actions/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper flex-grow-1">
            <div class="admin-header">
                <div class="container">
                    <h1><i class="fas fa-exchange-alt"></i> Manage Exchange Rates</h1>
                </div>
            </div>

            <div class="container">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <div class="admin-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>Current Exchange Rates</h3>
                        <button type="button" class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addRateModal">
                            <i class="fas fa-plus"></i> Add New Rate
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Base Currency</th>
                                    <th>Target Currency</th>
                                    <th>Rate</th>
                                    <th>Effective Date</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exchange_rates as $rate): ?>
                                    <tr>
                                        <td><?php echo $rate['id']; ?></td>
                                        <td><?php echo $rate['base_currency']; ?></td>
                                        <td><?php echo $rate['target_currency']; ?></td>
                                        <td><?php echo number_format($rate['rate'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($rate['effective_date'])); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($rate['updated_at'])); ?></td>
                                        <td class="action-btns">
                                            <button type="button" class="btn btn-sm btn-admin" 
                                                    data-bs-toggle="modal" data-bs-target="#editRateModal<?php echo $rate['id']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="exchange_rates.php?delete=<?php echo $rate['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this exchange rate?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal for each rate -->
                                    <div class="modal fade" id="editRateModal<?php echo $rate['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Exchange Rate</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo $rate['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Base Currency</label>
                                                            <input type="text" class="form-control" value="<?php echo $rate['base_currency']; ?>" readonly>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Target Currency</label>
                                                            <input type="text" class="form-control" value="<?php echo $rate['target_currency']; ?>" readonly>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="rate" class="form-label">Rate</label>
                                                            <input type="number" step="0.01" class="form-control" name="rate" 
                                                                   value="<?php echo $rate['rate']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="effective_date" class="form-label">Effective Date</label>
                                                            <input type="date" class="form-control" name="effective_date" 
                                                                   value="<?php echo $rate['effective_date']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="update_rate" class="btn btn-admin">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Rate Modal -->
    <div class="modal fade" id="addRateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Exchange Rate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="base_currency" class="form-label">Base Currency</label>
                            <select class="form-select" name="base_currency" required>
                                <option value="USD" selected>USD (US Dollar)</option>
                                <option value="EUR">EUR (Euro)</option>
                                <option value="GBP">GBP (British Pound)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="target_currency" class="form-label">Target Currency</label>
                            <select class="form-select" name="target_currency" required>
                                <option value="RWF" selected>RWF (Rwandan Franc)</option>
                                <option value="UGX">UGX (Ugandan Shilling)</option>
                                <option value="KES">KES (Kenyan Shilling)</option>
                                <option value="TZS">TZS (Tanzanian Shilling)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rate" class="form-label">Rate</label>
                            <input type="number" step="0.01" class="form-control" name="rate" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="effective_date" class="form-label">Effective Date</label>
                            <input type="date" class="form-control" name="effective_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_rate" class="btn btn-admin">Add Rate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('navSidebar').classList.toggle('show');
        });
        
        // Set today's date as default for effective date in add form
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('#addRateModal input[name="effective_date"]').value = today;
        });
    </script>
</body>
</html>