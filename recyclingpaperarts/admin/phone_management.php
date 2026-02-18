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
ini_set('display_errors', 2);
ini_set('display_startup_errors', 2);
error_reporting(E_ALL);

require_once('../includes/db.php');
require_once('../includes/phone_util.php');

// Get statistics
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE phone_number IS NOT NULL"))['count'] ?? 0;
$valid_phones = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE phone_number REGEXP '^[0-9]{10,15}$'"))['count'] ?? 0;
$invalid_phones = $total_users - $valid_phones;

// Get recent phone activities (you can add a phone_log table for this)
$recent_activities = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phone Number Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
                        <a class="nav-link" href="phone_management.php"><i class="fas fa-phone me-1"></i>Phone Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="support_phone.php"><i class="fas fa-headset me-1"></i>Support Phone</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php"><i class="fas fa-box me-1"></i>Products</a>
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

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-phone me-2"></i>Phone Number Management</h2>
                <p class="text-muted">Manage and monitor all phone numbers in the system</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stats-card p-4 text-center">
                    <i class="fas fa-users fa-2x text-primary mb-3"></i>
                    <div class="stat-number text-primary"><?php echo $total_users; ?></div>
                    <div>Total Users with Phones</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card p-4 text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                    <div class="stat-number text-success"><?php echo $valid_phones; ?></div>
                    <div>Valid Phone Numbers</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card p-4 text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                    <div class="stat-number text-warning"><?php echo $invalid_phones; ?></div>
                    <div>Potentially Invalid</div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Phone Actions Panel -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Phone Management Tools</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" onclick="exportPhones()">
                                <i class="fas fa-download me-2"></i>Export All Phones
                            </button>
                            <button class="btn btn-info" onclick="validateAllPhones()">
                                <i class="fas fa-check-circle me-2"></i>Validate All Phones
                            </button>
                            <button class="btn btn-warning" onclick="findDuplicates()">
                                <i class="fas fa-copy me-2"></i>Find Duplicates
                            </button>
                            <button class="btn btn-secondary" onclick="cleanupPhones()">
                                <i class="fas fa-broom me-2"></i>Cleanup Invalid Phones
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Quick Insights</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Validation Rate:</strong> <?php echo $total_users > 0 ? round(($valid_phones/$total_users)*100, 1) : 0; ?>%</p>
                        <p><strong>Needs Attention:</strong> <?php echo $invalid_phones; ?> records</p>
                        <p><strong>Last Updated:</strong> <?php echo date('M j, Y g:i A'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Phone Analysis Chart -->
            <div class="col-md-8 mb-4">
                <div class="chart-container">
                    <h5><i class="fas fa-chart-bar me-2"></i>Phone Number Analysis</h5>
                    <canvas id="phoneChart" height="100"></canvas>
                </div>

                <!-- Recent Activities -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Phone Activities</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_activities)): ?>
                            <p class="text-muted text-center py-4">No recent phone activities recorded</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Action</th>
                                            <th>User</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_activities as $activity): ?>
                                        <tr>
                                            <td><?php echo $activity['date']; ?></td>
                                            <td><?php echo $activity['action']; ?></td>
                                            <td><?php echo $activity['user']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $activity['status'] === 'success' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($activity['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize chart
        const ctx = document.getElementById('phoneChart').getContext('2d');
        const phoneChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Valid Phones', 'Invalid Phones'],
                datasets: [{
                    data: [<?php echo $valid_phones; ?>, <?php echo $invalid_phones; ?>],
                    backgroundColor: ['#28a745', '#ffc107'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Functions
        function exportPhones() {
            if (confirm('Export all phone numbers to CSV file?')) {
                window.location.href = '../actions/export_phones.php';
            }
        }

        function validateAllPhones() {
            if (confirm('Validate all phone numbers in the system?')) {
                // Implementation would go here
                alert('Phone validation started. This may take a moment...');
            }
        }

        function findDuplicates() {
            if (confirm('Search for duplicate phone numbers?')) {
                // Implementation would go here
                alert('Duplicate search initiated...');
            }
        }

        function cleanupPhones() {
            if (confirm('Remove or flag invalid phone numbers?')) {
                // Implementation would go here
                alert('Phone cleanup process started...');
            }
        }
    </script>
</body>
</html>