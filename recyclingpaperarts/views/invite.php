<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}
$referralCode = $_SESSION['referral_code'];

// Create a referral link
$baseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/views/register.php";
$inviteLink = $baseURL . "?referral_code=" . urlencode($referralCode);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Program | Recycling Paper Arts</title>
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

        .btn-outline-primary {
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .referral-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            padding: 32px;
            margin: 32px auto;
            max-width: 800px;
            transition: all 0.3s ease;
        }

        .referral-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 48px rgba(31, 38, 135, 0.25);
        }

        .referral-header {
            color: var(--primary);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 24px;
            text-align: center;
        }

        .referral-code {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            padding: 16px;
            border-radius: 12px;
            margin: 24px 0;
            text-align: center;
            border: 2px dashed var(--primary);
        }

        .referral-link {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 16px;
            border-radius: 12px;
            margin: 24px 0;
            word-break: break-all;
            border: 1px solid rgba(255, 255, 255, 0.18);
            font-size: 14px;
        }

        .bonus-rules {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 24px;
            border-radius: 12px;
            margin: 32px 0;
            border-left: 4px solid var(--primary);
        }

        .bonus-rules h4 {
            color: var(--primary);
            margin-bottom: 16px;
        }

        .bonus-rules ul {
            padding-left: 20px;
        }

        .bonus-rules li {
            margin-bottom: 12px;
            color: var(--text);
        }

        .copy-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .copy-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), #047857);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .copy-btn i {
            margin-right: 8px;
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
            .referral-card {
                padding: 24px;
                margin: 24px 16px;
            }
            
            .referral-code {
                font-size: 22px;
            }
            
            .referral-link {
                font-size: 12px;
                padding: 12px;
            }
        }

        @media (max-width: 576px) {
            .referral-code {
                font-size: 18px;
            }
            
            .section-title {
                font-size: 28px;
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
                        <a class="nav-link active" href="invite.php"><i class="fas fa-users me-1"></i> Agent</a>
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
            <h1 class="section-title"><i class="fas fa-handshake me-3"></i> Referral Program</h1>
            <p class="lead text-muted">Invite friends and earn bonuses on their investments</p>
        </div>

        <!-- Referral Card -->
        <div class="referral-card">
            <div class="referral-content">
                <h3 class="referral-header">Your Unique Referral Code</h3>
                
                <div class="referral-code">
                    <?php echo $referralCode; ?>
                </div>
                
                <p class="text-center text-muted">Share this code with friends to invite them to join Recycling Paper Arts</p>
                
                <div class="referral-link" id="invite-link">
                    <?php echo $inviteLink; ?>
                </div>
                
                <div class="text-center">
                    <button class="copy-btn" id="copyButton">
                        <i class="fas fa-copy"></i> Copy Referral Link
                    </button>
                </div>
                
                <div class="bonus-rules">
                    <h4><i class="fas fa-gift me-2"></i> Bonus Structure</h4>
                    <ul>
                        <li><strong>Level 1 Referrals:</strong> Earn 6% of their investment amount</li>
                        <li><strong>Level 2 Referrals:</strong> Earn 3% of their investment amount</li>
                    </ul>
                    <p class="mt-3"><i class="fas fa-info-circle me-2"></i> Bonuses are credited automatically and can be withdrawn anytime.</p>
                </div>
                
                <div class="text-center mt-4">
                    <a href="client_dashboard.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                    </a>
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

        $(document).ready(function() {
            // Copy Referral Link
            $('#copyButton').click(function() {
                var copyText = document.getElementById("invite-link").textContent;
                navigator.clipboard.writeText(copyText).then(function() {
                    // Change button text temporarily
                    var originalText = $(this).html();
                    $(this).html('<i class="fas fa-check"></i> Copied!');
                    
                    // Revert after 2 seconds
                    setTimeout(function() {
                        $('#copyButton').html('<i class="fas fa-copy"></i> Copy Referral Link');
                    }, 2000);
                    
                    // Show floating notification
                    $('<div class="alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3">Link copied to clipboard!</div>')
                        .appendTo('body')
                        .delay(2000)
                        .fadeOut(400, function() {
                            $(this).remove();
                        });
                }.bind(this));
            });

            // Create particles
            createParticles();
        });
    </script>
</body>
</html>