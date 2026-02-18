<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in and has a 'client' role
if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

// Fetch the user's details from the session
$phoneNumber = $_SESSION['phone_number'];
$referralCode = $_SESSION['referral_code'];
$userId = $_SESSION['user_id'];

include '../includes/db.php'; // Include database connection
include '../includes/function.php'; // Include helper functions
include '../includes/support_phone_util.php'; // Include support phone utilities

// Fetch the user's balance, referral bonus, first name, and last name from the users table
$sql = "SELECT balance, referral_bonus, fname, lname FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($balance, $referralBonus, $fname, $lname);
$stmt->fetch();
$stmt->close();

// Fetch the total profit (daily income) from the investments table
$sql = "SELECT SUM(daily_profit) as total_daily_income FROM investments WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalDailyIncome);
$stmt->fetch();
$stmt->close();

// Fetch the user's social media links from the database
$sql = "SELECT facebook, twitter, telegram, whatsapp FROM social_links";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($facebookLink, $twitterLink, $linkedinLink, $instagramLink);
$stmt->fetch();
$stmt->close();

// Close the connection
$conn->close();

// Check if name is missing
$nameMissing = empty($fname) || empty($lname);

// Function to format currency in USD
function formatUSD($amount) {
    return '$' . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Recycling Paper Arts</title>
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated gradient background */
        .gradient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            z-index: -2;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Glass morphism overlay */
        .glass-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(100px);
            z-index: -1;
        }

        /* Floating particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: float-particle 20s infinite;
        }

        .particle:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 60px; height: 60px; left: 30%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 100px; height: 100px; left: 50%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 70px; height: 70px; left: 70%; animation-delay: 6s; }
        .particle:nth-child(5) { width: 90px; height: 90px; left: 85%; animation-delay: 8s; }

        @keyframes float-particle {
            0%, 100% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 0.3; }
            90% { opacity: 0.3; }
            100% { transform: translateY(-100px) scale(1); }
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--dark) 0%, var(--dark-light) 100%);
            color: white;
            padding: 20px 0;
            position: relative;
            z-index: 10;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--primary);
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Navigation */
        .side-nav {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 250px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            z-index: 1000;
            padding-top: 100px;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        .side-nav.active {
            transform: translateX(0);
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .nav-item i {
            margin-right: 15px;
            font-size: 20px;
            width: 24px;
            text-align: center;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.25);
            border-left: 4px solid var(--primary);
            color: white;
        }

        .nav-item.active {
            font-weight: bold;
        }

        .nav-toggle {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 1100;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        /* Main Content */
        .main-content {
            margin-left: 0;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .main-content.shifted {
            margin-left: 250px;
        }

        /* Account Card */
        .account-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 2.5rem;
            margin: 2rem auto;
            max-width: 1200px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .account-content {
            position: relative;
            z-index: 2;
        }

        .account-header {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .account-info {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .account-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .info-label {
            font-size: 0.9rem;
            color: var(--text-light);
            font-weight: 500;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text);
        }

        .balance-value {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .income-value {
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .action-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            transition: all 0.4s ease;
        }

        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .action-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text);
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 0.8rem;
        }

        .action-link {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.8rem;
            color: var(--text);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .action-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            transition: left 0.3s ease;
            z-index: -1;
        }

        .action-link:hover::before {
            left: 0;
        }

        .action-link:hover {
            color: white;
            transform: translateX(8px);
        }

        .action-link i {
            width: 24px;
            text-align: center;
            margin-right: 1rem;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .action-link:hover i {
            transform: scale(1.2);
        }

        .logout-btn {
            background: linear-gradient(135deg, var(--secondary), #ec4899);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            width: 100%;
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .logout-btn i {
            margin-right: 0.5rem;
        }

        .warning-alert {
            background: rgba(251, 191, 36, 0.15);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .warning-alert .alert-link {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
        }

        .warning-alert .alert-link:hover {
            text-decoration: underline;
        }

        /* Social Media Links */
        .social-media-links {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .social-media-links a {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
        }

        .social-media-links a:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content.shifted {
                margin-left: 250px;
            }
            
            .side-nav {
                width: 250px;
            }
        }

        @media (max-width: 992px) {
            .account-card {
                padding: 2rem;
            }
            
            .main-content.shifted {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .account-header {
                font-size: 2rem;
            }
            
            .info-value {
                font-size: 1.1rem;
            }
            
            .balance-value {
                font-size: 1.5rem;
            }
            
            .income-value {
                font-size: 1.3rem;
            }
            
            .action-card {
                padding: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 1rem;
            }
            
            .account-card {
                padding: 1.5rem;
            }
            
            .social-media-links {
                bottom: 1rem;
                right: 1rem;
            }
            
            .social-media-links a {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }
        }

        /* Loading animation */
        .loading-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .loading-animation.active {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body>
    <div class="gradient-bg"></div>
    <div class="glass-overlay"></div>
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Loading Animation -->
    <div class="loading-animation" id="loadingAnimation">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Navigation Toggle -->
    <button class="nav-toggle" id="navToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Side Navigation -->
    <nav class="side-nav" id="sideNav">
        <a href="client_dashboard.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="purchased.php" class="nav-item">
            <i class="fas fa-chart-line"></i>
            <span>Income</span>
        </a>
        <a href="invite.php" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Agent</span>
        </a>
        <a href="account.php" class="nav-item active">
            <i class="fas fa-user"></i>
            <span>Personal</span>
        </a>
    </nav>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="header">
            <div class="container">
                <div class="logo-container">
                    <div class="logo">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="company-name">Recycling Paper Arts</div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="account-card">
                <div class="account-content">
                    <h2 class="account-header"><i class="fas fa-user-circle"></i> My Account</h2>
                    
                    <?php if ($nameMissing): ?>
                        <div class="alert warning-alert">
                            <i class="fas fa-exclamation-triangle"></i> Your name is missing. Please <a href="../actions/edit_profile.php" class="alert-link">update your profile</a>.
                        </div>
                    <?php endif; ?>
                    
                    <!-- Account Information Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="account-info">
                                <div class="info-label">Full Name</div>
                                <div class="info-value">
                                    <?php echo $nameMissing ? "Not Set" : htmlspecialchars($fname) . " " . htmlspecialchars($lname); ?>
                                </div>
                            </div>
                            
                            <div class="account-info">
                                <div class="info-label">Phone Number</div>
                                <div class="info-value"><?php echo htmlspecialchars($phoneNumber); ?></div>
                            </div>
                            
                            <div class="account-info">
                                <div class="info-label">Referral Code</div>
                                <div class="info-value"><?php echo htmlspecialchars($referralCode); ?></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="account-info">
                                <div class="info-label">Account Balance</div>
                                <div class="info-value balance-value"><?php echo formatUSD($balance); ?></div>
                            </div>
                            
                            <div class="account-info">
                                <div class="info-label">Project Revenue</div>
                                <div class="info-value income-value"><?php echo formatUSD($totalDailyIncome); ?></div>
                            </div>
                            
                            <div class="account-info">
                                <div class="info-label">Invitation Income</div>
                                <div class="info-value income-value"><?php echo formatUSD($referralBonus); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <hr style="border-color: rgba(0, 0, 0, 0.1); margin: 2rem 0;">
                    
                    <!-- Account Actions -->
                    <h4 class="text-center mb-4" style="color: var(--text); font-weight: 600;">
                        <i class="fas fa-cog"></i> Account Management
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="action-card">
                                <h5 class="action-title"><i class="fas fa-lock"></i> Security</h5>
                                <a href="change_password.php" class="action-link">
                                    <i class="fas fa-key"></i> Change Password
                                </a>
                                <a href="binding_bank.php" class="action-link">
                                    <i class="fas fa-university"></i> Bank Details
                                </a>
                                <a href="transaction_history.php" class="action-link">
                                    <i class="fas fa-history"></i> Transaction History
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="action-card">
                                <h5 class="action-title"><i class="fas fa-wallet"></i> Finances</h5>
                                <a href="recharge.php" class="action-link">
                                    <i class="fas fa-plus-circle"></i> Recharge Account
                                </a>
                                <a href="withdrawal.php" class="action-link">
                                    <i class="fas fa-minus-circle"></i> Withdraw Funds
                                </a>
                                <!-- <a href="my_wallet.php" class="action-link"><i class="fas fa-wallet"></i> My Wallet</a> -->
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="action-card">
                                <h5 class="action-title"><i class="fas fa-users"></i> Network</h5>
                                <a href="invite.php" class="action-link">
                                    <i class="fas fa-user-plus"></i> Invite Friends
                                </a>
                                <a href="my_team.php" class="action-link">
                                    <i class="fas fa-users"></i> My Team
                                </a>
                                <a href="../actions/edit_profile.php" class="action-link">
                                    <i class="fas fa-user-edit"></i> Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="../actions/logout.php" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Media Links -->
    <div class="social-media-links">
        <?php include 'Fetch_Links.php'; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Page loading animation
        window.addEventListener('load', function() {
            const loadingAnimation = document.getElementById('loadingAnimation');
            setTimeout(() => {
                loadingAnimation.classList.remove('active');
            }, 500);
        });

        // Navigation Toggle
        $(document).ready(function() {
            $('#navToggle').click(function() {
                $('#sideNav').toggleClass('active');
                $('#mainContent').toggleClass('shifted');
            });

            // Close nav when clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('#sideNav, #navToggle').length) {
                    $('#sideNav').removeClass('active');
                    $('#mainContent').removeClass('shifted');
                }
            });

            // Smooth scrolling for anchor links
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                }
            });

            // Add hover effects to cards
            $('.action-card').hover(
                function() {
                    $(this).addClass('shadow-lg');
                },
                function() {
                    $(this).removeClass('shadow-lg');
                }
            );
        });

        // Parallax effect for background
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            document.body.style.transform = `translateY(${rate}px)`;
        });
    </script>
</body>
</html>