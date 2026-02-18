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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize user input
    $bankName = htmlspecialchars($_POST['bank_name']);
    $accountNumber = htmlspecialchars($_POST['account_number']);
    $accountHolder = htmlspecialchars($_POST['account_holder']);

    // Insert or update bank details in the database
    $sql = "REPLACE INTO user_banks (user_id, bank_name, account_number, account_holder) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $userId, $bankName, $accountNumber, $accountHolder);

    if ($stmt->execute()) {
        $successMessage = "Withdrawal details successfully updated!";
    } else {
        $errorMessage = "Failed to update bank details. Please try again.";
    }

    $stmt->close();
}

$sql = "SELECT bank_name, account_number, account_holder FROM user_banks WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($bankName, $accountNumber, $accountHolder);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Details | Recycling Paper Arts</title>
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

        .bank-card {
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

        .bank-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 48px rgba(31, 38, 135, 0.25);
        }

        .bank-header {
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

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 12px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.9);
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
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
            .bank-card {
                padding: 24px;
                margin: 24px 16px;
            }
            
            .bank-header {
                font-size: 20px;
            }
        }

        @media (max-width: 576px) {
            .bank-card {
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
            <h1 class="section-title"><i class="fas fa-university me-3"></i> Withdrawal Account Details</h1>
            <p class="lead text-muted">Set up your withdrawal method for receiving payments</p>
        </div>

        <div class="bank-card">
            <h3 class="bank-header"><i class="fas fa-university me-2"></i> Withdrawal Account Details</h3>

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

            <!-- Withdrawal binding form -->
            <form action="binding_bank.php" method="POST">
                <div class="mb-4">
                    <label for="bank_name" class="form-label"><i class="fas fa-wallet me-1"></i> Withdrawal Method</label>
                    <select name="bank_name" id="bank_name" class="form-select" required>
                        <option value="">Select your Method</option>
                        <option value="MTN Mobile Money" <?php echo (isset($bankName) && $bankName == 'MTN Mobile Money') ? 'selected' : ''; ?>>MTN Mobile Money</option>
                        <option value="Airtel Money" <?php echo (isset($bankName) && $bankName == 'Airtel Money') ? 'selected' : ''; ?>>Airtel Money</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="account_number" class="form-label"><i class="fas fa-phone me-1"></i> Account Number</label>
                    <input type="text" name="account_number" id="account_number" minlength="10" class="form-control" required 
                           value="<?php echo isset($accountNumber) ? htmlspecialchars($accountNumber) : ''; ?>">
                    <small class="text-muted">Enter your 10-digit mobile money number</small>
                </div>

                <div class="mb-4">
                    <label for="account_holder" class="form-label"><i class="fas fa-user me-1"></i> Account Holder Name</label>
                    <input type="text" name="account_holder" id="account_holder" class="form-control" required 
                           value="<?php echo isset($accountHolder) ? htmlspecialchars($accountHolder) : ''; ?>">
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save me-2"></i> Save Withdrawal Details
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
            // Validate account number length
            $('#account_number').on('input', function() {
                if ($(this).val().length < 10) {
                    this.setCustomValidity('Account number must be at least 10 digits');
                } else {
                    this.setCustomValidity('');
                }
            });

            // Create particles
            createParticles();
        });
    </script>
</body>
</html>