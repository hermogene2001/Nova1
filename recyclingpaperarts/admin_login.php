<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if the user is already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
}

// Process admin login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('includes/db.php');  // Include your database connection here

    $phone = $_POST['phone_number'];
    $password = $_POST['password'];

    // Only allow admin role login on this page
    $sql = "SELECT * FROM users WHERE phone_number = ? AND role = 'admin'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session for logged-in admin
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['phone_number'] = $user['phone_number'];
        $_SESSION['referral_code'] = $user['referral_code'];

        header("Location: admin/dashboard.php");
        exit();
    } else {
        $error_message = "Invalid admin credentials or access denied!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycling Paper Arts - Admin Portal</title>
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

        /* Admin Portal Link */
        .admin-portal-link {
            position: fixed;
            top: 24px;
            right: 24px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .admin-portal-link:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }

        .container-fluid {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1200px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Brand Section */
        .brand-section {
            background: linear-gradient(135deg, var(--dark) 0%, var(--dark-light) 100%);
            padding: 80px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .brand-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .brand-content {
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .brand-logo i {
            font-size: 3.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-logo h1 {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            line-height: 1.2;
            margin: 0;
        }

        .brand-tagline {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 48px;
            font-weight: 400;
        }

        .features-list {
            list-style: none;
        }

        .features-list li {
            padding: 16px 0;
            display: flex;
            align-items: center;
            gap: 16px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .features-list li:hover {
            padding-left: 12px;
            color: white;
        }

        .features-list li i {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        /* Login Section */
        .login-section {
            padding: 80px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--dark), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }

        .alert {
            border-radius: 16px;
            border: none;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }

        .form-group {
            position: relative;
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text);
            font-weight: 600;
            font-size: 0.875rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.125rem;
            z-index: 10;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            height: 56px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 0 20px 0 52px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
            color: var(--text);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .form-control:focus + .input-icon {
            color: var(--primary);
        }

        .btn-login {
            width: 100%;
            height: 56px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 700;
            color: white;
            margin-top: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(16, 185, 129, 0.4);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .links-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .links-section a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .links-section a:hover {
            color: var(--primary-dark);
            transform: translateX(2px);
        }

        .divider {
            margin: 40px 0;
            text-align: center;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }

        .divider span {
            background: white;
            padding: 0 20px;
            color: var(--text-light);
            font-weight: 600;
            font-size: 0.875rem;
            position: relative;
        }

        .info-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .info-card {
            background: linear-gradient(135deg, #f8fafc, white);
            padding: 24px;
            border-radius: 16px;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
            border-color: var(--primary);
        }

        .info-card i {
            font-size: 2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
            display: block;
        }

        .info-card h4 {
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 1rem;
            font-weight: 700;
        }

        .info-card p {
            color: var(--text-light);
            font-size: 0.875rem;
            line-height: 1.5;
        }

        @media (max-width: 992px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }
            
            .brand-section {
                padding: 60px 40px;
            }
            
            .login-section {
                padding: 60px 40px;
            }

            .admin-portal-link {
                position: static;
                margin: 20px auto;
                display: inline-flex;
            }

            .container-fluid {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .brand-section,
            .login-section {
                padding: 40px 24px;
            }

            .login-title {
                font-size: 2rem;
            }

            .brand-logo h1 {
                font-size: 1.5rem;
            }

            .info-cards {
                grid-template-columns: 1fr;
            }

            .links-section {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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

    <!-- Admin Portal Access Link -->
    <a href="index.php" class="admin-portal-link">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Login</span>
    </a>

    <div class="container-fluid">
        <div class="login-wrapper">
            <!-- Brand Section -->
            <div class="brand-section">
                <div class="brand-content">
                    <div class="brand-logo">
                        <i class="fas fa-newspaper"></i>
                        <h1>Recycling Paper Arts</h1>
                    </div>
                    <p class="brand-tagline">Editorial Center Access</p>
                    
                    <ul class="features-list">
                        <li>
                            <i class="fas fa-users-cog"></i>
                            <span>Subscriber Management</span>
                        </li>
                        <li>
                            <i class="fas fa-chart-pie"></i>
                            <span>Readership Analytics</span>
                        </li>
                        <li>
                            <i class="fas fa-cogs"></i>
                            <span>Content Configuration</span>
                        </li>
                        <li>
                            <i class="fas fa-database"></i>
                            <span>Article Management</span>
                        </li>
                        <li>
                            <i class="fas fa-shield-check"></i>
                            <span>Editorial Controls</span>
                        </li>
                        <li>
                            <i class="fas fa-bell"></i>
                            <span>Content Monitoring</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Login Section -->
            <div class="login-section">
                <div class="login-header">
                    <h2 class="login-title">Editorial Access</h2>
                    <p class="login-subtitle">Secure editorial portal</p>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error_message; ?></span>
                    </div>
                <?php endif; ?>

                <form action="admin_login.php" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="admin_phone">Admin Phone Number</label>
                        <div class="input-wrapper">
                            <input type="text" class="form-control" name="phone_number" id="admin_phone" placeholder="Enter admin phone number" required>
                            <i class="fas fa-user-shield input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="admin_password">Admin Password</label>
                        <div class="input-wrapper">
                            <input type="password" class="form-control" name="password" id="admin_password" placeholder="Enter admin password" minlength="6" required>
                            <i class="fas fa-key input-icon"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Access Editorial Panel
                    </button>
                </form>

                <div class="links-section">
                    <a href="../index.php">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Main Site</span>
                    </a>
                </div>

                <div class="divider">
                    <span>Security Notice</span>
                </div>

                <div class="info-cards">
                    <div class="info-card">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Restricted Access</h4>
                        <p>This portal is exclusively for authorized editors</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-bell"></i>
                        <h4>Monitoring Active</h4>
                        <p>All access attempts are logged and monitored</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.querySelector('.btn-login');
            const inputs = document.querySelectorAll('.form-control');

            // Add focus effects
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('.input-icon').style.transform = 'translateY(-50%) scale(1.1)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.querySelector('.input-icon').style.transform = 'translateY(-50%) scale(1)';
                });
            });

            // Handle form submission
            form.addEventListener('submit', function(e) {
                loginBtn.innerHTML = '<span class="spinner"></span> Authenticating...';
                loginBtn.disabled = true;
            });

            // Add entrance animations
            const animateElements = document.querySelectorAll('.login-wrapper, .admin-portal-link');
            animateElements.forEach((el, index) => {
                el.style.animation = `slideIn 0.6s ease ${index * 0.1}s both`;
            });
        });
    </script>
</body>
</html>