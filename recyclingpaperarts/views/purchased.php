<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

// Database connection
require_once('../includes/db.php');

// Fetch active purchases (standard products)
$client_id = $_SESSION['user_id'];
$active_query = "
    SELECT products.name, products.price, products.daily_earning, products.cycle, purchases.purchase_date, products.image
    FROM purchases 
    JOIN products ON purchases.product_id = products.id 
    WHERE purchases.client_id = ? 
    AND (CURDATE() <= DATE_ADD(purchases.purchase_date, INTERVAL products.cycle DAY))
    ORDER BY purchases.purchase_date DESC
";
$active_stmt = $conn->prepare($active_query);
$active_stmt->bind_param("i", $client_id);
$active_stmt->execute();
$active_result = $active_stmt->get_result();

// Fetch completed purchases (standard products)
$completed_query = "
    SELECT products.name, products.price, products.daily_earning, products.cycle, purchases.purchase_date, products.image
    FROM purchases 
    JOIN products ON purchases.product_id = products.id 
    WHERE purchases.client_id = ? 
    AND (CURDATE() > DATE_ADD(purchases.purchase_date, INTERVAL products.cycle DAY))
    ORDER BY purchases.purchase_date DESC
";
$completed_stmt = $conn->prepare($completed_query);
$completed_stmt->bind_param("i", $client_id);
$completed_stmt->execute();
$completed_result = $completed_stmt->get_result();



// Close the database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Investments | Recycling Paper Arts</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --secondary: #6366f1;
            --accent: #f59e0b;
            --dark: #0f172a;
            --dark-light: #1e293b;
            --text: #334155;
            --text-light: #64748b;
            --bg: #f8fafc;
            --surface: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated gradient background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5bc);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            z-index: -2;
            opacity: 0.3;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating particles effect */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
        }

        .nav-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .nav-link {
            color: var(--text) !important;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 4px 2px;
            font-weight: 500;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary);
            color: white !important;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .investment-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            transition: all 0.3s ease;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .investment-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px rgba(31, 38, 135, 0.25);
        }

        .investment-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .investment-body {
            padding: 24px;
        }

        .investment-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--dark);
        }

        .investment-stat {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .stat-label {
            font-weight: 600;
            color: var(--text-light);
        }

        .stat-value {
            font-weight: 700;
            color: var(--dark);
        }

        .price-value {
            color: var(--primary);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .status-completed {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .status-withdrawn {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
        }

        .status-penalized {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .section-title {
            font-size: 28px;
            font-weight: 700;
            margin: 32px 0 24px;
            position: relative;
            padding-bottom: 16px;
            color: var(--dark);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .nav-tabs {
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 24px;
        }

        .nav-tabs .nav-link {
            color: var(--text-light);
            border: none;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 12px 12px 0 0;
        }

        .nav-tabs .nav-link:hover {
            color: var(--text);
            background-color: rgba(255, 255, 255, 0.3);
        }

        .nav-tabs .nav-link.active {
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
        }

        /* Risk Level Badges */
        .risk-low {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .risk-medium {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .risk-high {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .investment-image {
                height: 150px;
            }
            
            .nav-tabs .nav-link {
                padding: 10px 15px;
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            .investment-image {
                height: 120px;
            }
            
            .nav-tabs .nav-link {
                padding: 8px 10px;
                font-size: 12px;
            }
            
            .investment-body {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg nav-container py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3" href="client_dashboard.php">
                <i class="fas fa-recycle me-2 text-primary"></i>
                Recycling Paper Arts
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="client_dashboard.php"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="purchased.php"><i class="fas fa-chart-line me-1"></i> Income</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="invite.php"><i class="fas fa-users me-1"></i> Agent</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="account.php"><i class="fas fa-user me-1"></i> Personal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../actions/logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="section-title"><i class="fas fa-briefcase me-3"></i> Investment Portfolio</h1>
            <p class="lead text-muted">View and manage your active and completed investments</p>
        </div>

        <div class="tab-content" id="investmentTabsContent">
            <!-- Standard Products Tab -->
            <div class="tab-pane fade show active" id="standard" role="tabpanel" aria-labelledby="standard-tab">
                <!-- Active Investments Section -->
                <h2 class="section-title"><i class="fas fa-bolt me-3"></i> Active Investments</h2>
                <div class="row g-4">
                    <?php if ($active_result->num_rows > 0) { ?>
                        <?php while($purchase = $active_result->fetch_assoc()) { 
                            $days_remaining = floor((strtotime($purchase['purchase_date'] . '+' . $purchase['cycle'] . ' days') - time()) / (60 * 60 * 24));
                            $days_remaining = max(0, $days_remaining);
                        ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="investment-card h-100">
                                    <img src="../uploads/<?php echo htmlspecialchars($purchase['image']); ?>" class="investment-image" alt="<?php echo htmlspecialchars($purchase['name']); ?>">
                                    <div class="investment-body">
                                        <h5 class="investment-title"><?php echo htmlspecialchars($purchase['name']); ?></h5>
                                        
                                        <div class="investment-stat">
                                            <span class="stat-label">Investment:</span>
                                            <span class="stat-value price-value"><?php echo number_format($purchase['price'], 0); ?> $</span>
                                        </div>
                                        
                                        <div class="investment-stat">
                                            <span class="stat-label">Daily Earnings:</span>
                                            <span class="stat-value"><?php echo number_format($purchase['daily_earning'], 0); ?> $</span>
                                        </div>
                                        
                                        <div class="investment-stat">
                                            <span class="stat-label">Days Remaining:</span>
                                            <span class="stat-value"><?php echo ceil($days_remaining); ?> days</span>
                                        </div>
                                        
                                        <div class="investment-stat">
                                            <span class="stat-label">Purchased On:</span>
                                            <span class="stat-value"><?php echo date("M d, Y", strtotime($purchase['purchase_date'])); ?></span>
                                        </div>
                                        
                                        <div class="text-center mt-3">
                                            <span class="status-badge status-active">Active</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="col-12">
                            <div class="glass-card p-5 text-center">
                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Active Investments</h5>
                                <p class="text-muted">You don't have any active standard investments currently.</p>
                                <a href="client_dashboard.php" class="btn btn-primary">Start Investing</a>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <!-- Completed Investments Section -->
                <h2 class="section-title"><i class="fas fa-check-circle me-3"></i> Completed Investments</h2>
                <div class="row g-4">
                    <?php if ($completed_result->num_rows > 0) { ?>
                        <?php while($purchase = $completed_result->fetch_assoc()) { ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="investment-card h-100">
                                    <img src="../uploads/<?php echo htmlspecialchars($purchase['image']); ?>" class="investment-image" alt="<?php echo htmlspecialchars($purchase['name']); ?>">
                                    <div class="investment-body">
                                        <h5 class="investment-title"><?php echo htmlspecialchars($purchase['name']); ?></h5>
                                        
                                        <div class="investment-stat">
                                            <span class="stat-label">Investment:</span>
                                            <span class="stat-value price-value"><?php echo number_format($purchase['price'], 0); ?> $</span>
                                        </div>
                                        
                                        <div class="investment-stat">
                                            <span class="stat-label">Daily Earnings:</span>
                                            <span class="stat-value"><?php echo number_format($purchase['daily_earning'], 0); ?> $</span>
                                        </div>
                                        
                                        <div class="investment-stat">
                                            <span class="stat-label">Total Return:</span>
                                            <span class="stat-value price-value"><?php echo number_format($purchase['daily_earning'] * $purchase['cycle'] + $purchase['price'], 0); ?> $</span>
                                        </div>
                                        
                                        <div class="investment-stat">
                                            <span class="stat-label">Completed On:</span>
                                            <span class="stat-value"><?php echo date("M d, Y", strtotime($purchase['purchase_date'] . '+' . $purchase['cycle'] . ' days')); ?></span>
                                        </div>
                                        
                                        <div class="text-center mt-3">
                                            <span class="status-badge status-completed">Completed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="col-12">
                            <div class="glass-card p-5 text-center">
                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Completed Investments</h5>
                                <p class="text-muted">You don't have any completed standard investments yet.</p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 20;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                // Random size and position
                const size = Math.random() * 20 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;
                
                // Random animation delay and duration
                particle.style.animationDelay = `${Math.random() * 5}s`;
                particle.style.animationDuration = `${Math.random() * 3 + 4}s`;
                
                particlesContainer.appendChild(particle);
            }
        }

        // Navigation Toggle
        $(document).ready(function() {
            // Create particles
            createParticles();
        });
    </script>
</body>
</html>