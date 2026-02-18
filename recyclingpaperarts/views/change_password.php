<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/db.php';

$userId = $_SESSION['user_id'];
$errorMessage = "";
$successMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $currentPassword = htmlspecialchars($_POST['current_password']);
    $newPassword = htmlspecialchars($_POST['new_password']);
    $confirmPassword = htmlspecialchars($_POST['confirm_password']);

    // Check if new password matches confirmation
    if ($newPassword !== $confirmPassword) {
        $errorMessage = "New password and confirmation password do not match.";
    } else {
        // Fetch the current password from the database
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($dbPassword);
        $stmt->fetch();
        $stmt->close();

        // Check if the entered current password is correct
        if (!password_verify($currentPassword, $dbPassword)) {
            $errorMessage = "Current password is incorrect.";
        } else {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update the password in the database
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $userId);

            if ($stmt->execute()) {
                $successMessage = "Password successfully changed!";
            } else {
                $errorMessage = "Failed to change password. Please try again.";
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | Recycling Paper Arts</title>
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

        .password-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            padding: 32px;
            margin: 32px auto;
            max-width: 600px;
            transition: all 0.3s ease;
        }

        .password-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 48px rgba(31, 38, 135, 0.25);
        }

        .password-header {
            color: var(--primary);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 2px solid rgba(16, 185, 129, 0.1);
            padding-bottom: 15px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.9);
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
        }

        .password-strength {
            height: 8px;
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 4px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            background-color: #dc3545;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .password-requirements {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .password-requirements ul {
            list-style-type: none;
            padding-left: 0;
        }

        .password-requirements li {
            margin-bottom: 5px;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 15px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), #047857);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        /* Alert Messages */
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            color: #10b981;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            color: #ef4444;
        }

        .section-title {
            font-size: 32px;
            font-weight: 700;
            margin: 40px 0 24px;
            position: relative;
            padding-bottom: 16px;
            text-align: center;
            color: var(--dark);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .password-card {
                padding: 24px;
                margin: 24px 16px;
            }
            
            .password-header {
                font-size: 20px;
            }
        }

        @media (max-width: 576px) {
            .password-card {
                margin: 20px 10px;
                padding: 20px;
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
                        <a class="nav-link" href="purchased.php"><i class="fas fa-chart-line me-1"></i> Income</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="invite.php"><i class="fas fa-users me-1"></i> Agent</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="account.php"><i class="fas fa-user me-1"></i> Personal</a>
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
            <h1 class="section-title"><i class="fas fa-key me-3"></i> Change Password</h1>
            <p class="lead text-muted">Update your account password securely</p>
        </div>

        <div class="password-card">
            <h3 class="password-header"><i class="fas fa-key me-2"></i> Change Password</h3>

            <!-- Display success or error message -->
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success mb-4">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $successMessage; ?>
                </div>
            <?php elseif (!empty($errorMessage)): ?>
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <!-- Password change form -->
            <form action="change_password.php" method="POST" id="passwordForm">
                <div class="mb-4">
                    <label for="current_password" class="form-label"><i class="fas fa-lock me-1"></i> Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label for="new_password" class="form-label"><i class="fas fa-lock me-1"></i> New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required minlength="8">
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="password-requirements">
                        <small class="text-muted">Password must be at least 8 characters long and contain:</small>
                        <ul class="mt-2">
                            <li id="lengthReq"><i class="fas fa-times me-1 text-danger"></i> At least 8 characters</li>
                            <li id="uppercaseReq"><i class="fas fa-times me-1 text-danger"></i> One uppercase letter</li>
                            <li id="lowercaseReq"><i class="fas fa-times me-1 text-danger"></i> One lowercase letter</li>
                            <li id="numberReq"><i class="fas fa-times me-1 text-danger"></i> One number</li>
                        </ul>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label"><i class="fas fa-lock me-1"></i> Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="8">
                    <div id="passwordMatch" class="text-danger small mt-1"></div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save me-2"></i> Change Password
                </button>
            </form>
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

        $(document).ready(function() {
            // Password strength checker
            $('#new_password').on('input', function() {
                const password = $(this).val();
                let strength = 0;
                
                // Check length
                if (password.length >= 8) {
                    strength += 1;
                    $('#lengthReq').css('color', 'green').find('i').removeClass('text-danger').addClass('text-success').removeClass('fa-times').addClass('fa-check');
                } else {
                    $('#lengthReq').css('color', 'red').find('i').removeClass('text-success').addClass('text-danger').removeClass('fa-check').addClass('fa-times');
                }
                
                // Check uppercase
                if (/[A-Z]/.test(password)) {
                    strength += 1;
                    $('#uppercaseReq').css('color', 'green').find('i').removeClass('text-danger').addClass('text-success').removeClass('fa-times').addClass('fa-check');
                } else {
                    $('#uppercaseReq').css('color', 'red').find('i').removeClass('text-success').addClass('text-danger').removeClass('fa-check').addClass('fa-times');
                }
                
                // Check lowercase
                if (/[a-z]/.test(password)) {
                    strength += 1;
                    $('#lowercaseReq').css('color', 'green').find('i').removeClass('text-danger').addClass('text-success').removeClass('fa-times').addClass('fa-check');
                } else {
                    $('#lowercaseReq').css('color', 'red').find('i').removeClass('text-success').addClass('text-danger').removeClass('fa-check').addClass('fa-times');
                }
                
                // Check number
                if (/\d/.test(password)) {
                    strength += 1;
                    $('#numberReq').css('color', 'green').find('i').removeClass('text-danger').addClass('text-success').removeClass('fa-times').addClass('fa-check');
                } else {
                    $('#numberReq').css('color', 'red').find('i').removeClass('text-success').addClass('text-danger').removeClass('fa-check').addClass('fa-times');
                }
                
                // Update strength bar
                const $bar = $('#passwordStrengthBar');
                let width = (strength / 4) * 100;
                let color = '#dc3545'; // red
                
                if (strength >= 2) color = '#ffc107'; // yellow
                if (strength >= 3) color = '#28a745'; // green
                
                $bar.css({
                    'width': width + '%',
                    'background-color': color
                });
            });
            
            // Password match checker
            $('#confirm_password').on('input', function() {
                const newPassword = $('#new_password').val();
                const confirmPassword = $(this).val();
                const $matchIndicator = $('#passwordMatch');
                
                if (confirmPassword.length > 0) {
                    if (newPassword === confirmPassword) {
                        $matchIndicator.text('Passwords match').css('color', 'green');
                    } else {
                        $matchIndicator.text('Passwords do not match').css('color', 'red');
                    }
                } else {
                    $matchIndicator.text('');
                }
            });

            // Create particles
            createParticles();
        });
    </script>
</body>
</html>