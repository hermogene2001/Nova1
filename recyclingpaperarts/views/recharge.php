<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}
// ini_set('display_errors', 2);
// ini_set('display_startup_errors', 2);
// error_reporting(E_ALL);

date_default_timezone_set("Africa/Kigali"); 

// Database connection
require_once('../includes/db.php');
include '../includes/function.php';

// Minimum and maximum deposit amounts in USD
$min_deposit_usd = 5;
$max_deposit_usd = 3000;

// Get exchange rate from admin settings
$exchange_rate_query = "SELECT rate FROM exchange_rates 
              WHERE base_currency = 'USD' AND target_currency = 'RWF'
              ORDER BY effective_date DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $exchange_rate_query);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $exchange_rate);
$has_exchange_rate = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Set default exchange rate if query failed
if (!$has_exchange_rate) {
    $exchange_rate = 1200; // Default fallback rate
}

// Calculate min/max in RWF
$min_deposit_rwf = $min_deposit_usd * $exchange_rate;
$max_deposit_rwf = $max_deposit_usd * $exchange_rate;

// Initialize variables
$selected_agent = null;

// Handle the recharge form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount_usd = floatval($_POST['amount_usd']);
    $client_id = $_SESSION['user_id'];
    $agent_id = intval($_POST['agent']);  // Selected agent
    
    // Convert to RWF
    $amount_rwf = $amount_usd * $exchange_rate;

    // Validate the amount
    if ($amount_usd < $min_deposit_usd || $amount_usd > $max_deposit_usd) {
        echo "<script>alert('The minimum deposit amount is $min_deposit_usd USD (".number_format($min_deposit_rwf)." RWF) AND maximum deposit amount is $max_deposit_usd USD (".number_format($max_deposit_rwf)." RWF). Please enter a valid amount.');</script>";
    } elseif ($amount_usd > 0) {
        // Insert recharge record in the recharges table with status 'pending'
        $insert_recharge_query = "INSERT INTO recharges (client_id, agent_id, amount, recharge_time, status) VALUES (?, ?, ?, NOW(), 'pending')";
        $stmt = mysqli_prepare($conn, $insert_recharge_query);
        mysqli_stmt_bind_param($stmt, "iid", $client_id, $agent_id, $amount_usd);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Simulate notification to agent
        echo "<script>alert('Recharge request successful! Please send ".number_format($amount_rwf)." RWF to the agent.');</script>";

        // Fetch selected agent's information
        $agent_query = "SELECT phone_number, CONCAT(fname, ' ', lname) AS name FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $agent_query);
        mysqli_stmt_bind_param($stmt, "i", $agent_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $selected_agent_phone, $selected_agent_name);
        $has_agent = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($has_agent) {
            $selected_agent = [
                'name' => $selected_agent_name,
                'phone' => $selected_agent_phone,
                'amount_rwf' => $amount_rwf
            ];
        }
    } else {
        echo "<script>alert('Invalid amount. Please enter a positive number.');</script>";
    }
}

// Fetch the current balance in USD
$current_balance_query = "SELECT balance FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $current_balance_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $current_balance_usd);
$has_balance = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Set default balance if query failed
if (!$has_balance) {
    $current_balance_usd = 0;
}

// Calculate balance in RWF
$current_balance_rwf = $current_balance_usd * $exchange_rate;

// Fetch all available agents
$agents_query = "SELECT id, phone_number, CONCAT(fname, ' ', lname) AS name FROM users WHERE role = 'agent'";
$stmt = mysqli_prepare($conn, $agents_query);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $agent_id, $agent_phone, $agent_name);

$agents = [];
while (mysqli_stmt_fetch($stmt)) {
    $agents[] = [
        'id' => $agent_id,
        'phone' => $agent_phone,
        'name' => $agent_name
    ];
}
mysqli_stmt_close($stmt);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recharge Account | Recycling Paper Arts</title>
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

        .recharge-container {
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

        .recharge-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 48px rgba(31, 38, 135, 0.25);
        }

        .exchange-rate {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 600;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .amount-converter {
            display: flex;
            gap: 15px;
            margin-bottom: 24px;
        }

        .amount-converter input {
            flex: 1;
        }

        .agent-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .btn-magazine {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-magazine:hover {
            background: linear-gradient(135deg, var(--primary-dark), #047857);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .copy-btn {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: var(--primary);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .copy-btn:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.05);
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

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            padding: 24px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .amount-converter {
                flex-direction: column;
            }
            
            .recharge-container {
                padding: 24px;
                margin: 24px 16px;
            }
            
            .exchange-rate {
                padding: 12px;
            }
        }

        @media (max-width: 576px) {
            .recharge-container {
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
            <h1 class="section-title"><i class="fas fa-wallet me-3"></i> Recharge Your Account</h1>
            <p class="lead text-muted">Add funds to your account using our secure payment system</p>
        </div>

        <div class="recharge-container">
            <h2 class="mb-4"><i class="fas fa-wallet me-2 text-primary"></i> Recharge Your Account</h2>
            
            <div class="exchange-rate">
                <i class="fas fa-exchange-alt me-2"></i> Current Exchange Rate: 1 USD = <?php echo number_format($exchange_rate); ?> RWF
            </div>
            
            <p class="mb-3">Your current balance: 
                <strong class="text-primary"><?php echo number_format($current_balance_usd, 2); ?> USD</strong> 
                (≈ <?php echo number_format($current_balance_rwf); ?> RWF)
            </p>
            
            <p class="text-danger mb-4"><strong><i class="fas fa-exclamation-triangle me-1"></i> Note:</strong> Minimum deposit: <?php echo $min_deposit_usd; ?> USD (<?php echo number_format($min_deposit_rwf); ?> RWF)</p>
            
            <form action="" method="POST" id="rechargeForm">
                <div class="mb-4">
                    <label for="amount_usd" class="form-label"><i class="fas fa-dollar-sign me-1"></i> Amount to Deposit (USD)</label>
                    <input type="number" class="form-control" id="amount_usd" name="amount_usd" 
                           required min="<?php echo $min_deposit_usd; ?>" max="<?php echo $max_deposit_usd; ?>" step="0.01">
                </div>
                
                <div class="mb-4">
                    <div class="amount-converter">
                        <div class="form-group flex-grow-1">
                            <label><i class="fas fa-calculator me-1"></i> Equivalent in RWF</label>
                            <input type="text" class="form-control" id="amount_rwf" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="agent" class="form-label"><i class="fas fa-user-tie me-1"></i> Select Agent</label>
                    <select class="form-select" id="agent" name="agent" required>
                        <option value="">-- Select an agent --</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?php echo $agent['id']; ?>">
                                <?php echo htmlspecialchars($agent['name'] ?? ''); ?> (<?php echo htmlspecialchars($agent['phone'] ?? ''); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="agent-card" id="agentDetails" style="display: none;">
                    <h5 class="mb-3"><i class="fas fa-user-tie me-2 text-primary"></i> Selected Agent Details</h5>
                    <p id="agentInfo" class="mb-0">
                        <!-- Agent details will be displayed here -->
                    </p>
                    
                    <div class="mt-3">
                        <h6 class="mb-2"><i class="fas fa-mobile-alt me-2"></i> Quick Payment Methods:</h6>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="#" id="quickPaymentLink" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-phone me-1"></i> 
                            </a>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-magazine mt-3">
                    <i class="fas fa-paper-plane me-2"></i> Submit Recharge Request
                </button>
            </form>

            <?php if ($selected_agent): ?>
                <div class="alert alert-success mt-4">
                    <h5 class="mb-3"><i class="fas fa-check-circle me-2"></i> Recharge Request Successful</h5>
                    <p class="mb-3">Please send <strong class="text-primary"><?php echo number_format($selected_agent['amount_rwf'] ?? 0); ?> RWF</strong> to:</p>
                    <div class="agent-card">
                        <p class="mb-0">
                            <strong><?php echo htmlspecialchars($selected_agent['name'] ?? ''); ?></strong><br>
                            <i class="fas fa-phone me-1"></i> Phone: <?php echo htmlspecialchars($selected_agent['phone'] ?? ''); ?>
                            <button class="btn btn-sm copy-btn ms-2" data-phone="<?php echo htmlspecialchars($selected_agent['phone'] ?? ''); ?>">
                                <i class="fas fa-copy"></i>
                            </button>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
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
            // Calculate RWF equivalent when USD amount changes
            $('#amount_usd').on('input', function() {
                const usdAmount = parseFloat($(this).val()) || 0;
                const exchangeRate = <?php echo json_encode($exchange_rate); ?>;
                const rwfAmount = usdAmount * exchangeRate;
                $('#amount_rwf').val(rwfAmount.toFixed(0) + ' RWF');
            });

            // Copy phone number
            $(document).on('click', '.copy-btn', function() {
                const phoneNumber = $(this).data('phone');
                navigator.clipboard.writeText(phoneNumber).then(() => {
                    alert('Phone number copied: ' + phoneNumber);
                }).catch(err => {
                    console.error('Could not copy text: ', err);
                });
            });

            // Form submission validation
            $('#rechargeForm').submit(function() {
                const usdAmount = parseFloat($('#amount_usd').val());
                const minAmount = <?php echo json_encode($min_deposit_usd); ?>;
                const maxAmount = <?php echo json_encode($max_deposit_usd); ?>;
                const agentId = $('#agent').val();
                
                if (usdAmount < minAmount || usdAmount > maxAmount) {
                    alert(`Amount must be between ${minAmount} USD and ${maxAmount} USD`);
                    return false;
                }
                
                if (!agentId) {
                    alert('Please select an agent');
                    return false;
                }
                
                return true;
            });
            
            // Show agent details when selected
            $('#agent').change(function() {
                const selectedOption = $(this).find('option:selected');
                if (selectedOption.val()) {
                    const agentText = selectedOption.text();
                    const phoneNumber = agentText.match(/\((\d+)\)/)?.[1] || '';
                    
                    $('#agentInfo').html(`
                        <strong>${agentText.split(' (')[0]}</strong><br>
                        <i class="fas fa-phone me-1"></i> Phone: ${phoneNumber}
                        <button class="btn btn-sm copy-btn ms-2" data-phone="${phoneNumber}">
                            <i class="fas fa-copy"></i>
                        </button>
                    `);
                    
                    $('#quickPaymentLink').html(`
                        <i class="fas fa-phone me-1"></i> *182*1*1*${phoneNumber}#
                    `).attr('href', `tel:*182*1*1*${phoneNumber}#`);
                    
                    $('#agentDetails').show();
                } else {
                    $('#agentDetails').hide();
                }
            });

            // Create particles
            createParticles();
        });
    </script>
</body>
</html>