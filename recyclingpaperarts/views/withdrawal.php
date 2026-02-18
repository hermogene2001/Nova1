<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../includes/db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
date_default_timezone_set("Africa/Kigali");

// Get exchange rate
$exchange_rate_query = "SELECT rate FROM exchange_rates WHERE base_currency = 'USD' AND target_currency = 'RWF' ORDER BY effective_date DESC LIMIT 1";
$exchange_result = mysqli_query($conn, $exchange_rate_query);
$exchange_row = mysqli_fetch_assoc($exchange_result);
$exchange_rate = $exchange_row['rate'] ?? 1430; // Default fallback rate

// Fetch user balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$balance_result = $result->fetch_assoc();
$balance_usd = $balance_result['balance'];
$balance_rwf = $balance_usd * $exchange_rate;

// Withdrawal settings
$min_withdrawal_usd = 1;
$max_withdrawal_usd = 3000;
$min_withdrawal_rwf = $min_withdrawal_usd * $exchange_rate;
$max_withdrawal_rwf = $max_withdrawal_usd * $exchange_rate;
$withdrawal_fee_percent = 8;

// Allowed withdrawal time (7:00 AM to 12:00 AM)
$allowed_withdrawal_time_start = '07:00';
$allowed_withdrawal_time_end = '00:00';
$current_time = date('H:i');

// Fetch available agents
$agents_query = "SELECT id, fname, phone_number FROM users WHERE role = 'agent' ";
$agents_result = mysqli_query($conn, $agents_query);
$agents = [];
while ($agent = mysqli_fetch_assoc($agents_result)) {
    $agents[] = $agent;
}

// Process withdrawal request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $withdraw_amount_usd = floatval($_POST['withdraw_amount_usd']);
    $agent_id = intval($_POST['agent_id']);
    $withdraw_amount_rwf = ($withdraw_amount_usd * $exchange_rate);
    $fee_amount_usd = $withdraw_amount_usd * ($withdrawal_fee_percent / 100);
    $withdraw_amount_rwf_after_fee = $withdraw_amount_rwf - ($fee_amount_usd * $exchange_rate);
    
    // Check daily withdrawal limit (1 per day)
    $daily_withdrawal_query = "
        SELECT COUNT(*) as count FROM withdrawals 
        WHERE client_id = $user_id 
        AND DATE(date) = CURDATE()
    ";
    $daily_result = mysqli_query($conn, $daily_withdrawal_query);
    $daily_row = mysqli_fetch_assoc($daily_result);
    $daily_count = $daily_row['count'] ?? 0;

    if ($daily_count >= 1) {
        echo "<script>alert('You can only make one withdrawal per day.'); window.location.href = 'withdrawal.php';</script>";
        exit;
    }

    // Validate amount
    if ($withdraw_amount_usd < $min_withdrawal_usd || $withdraw_amount_usd > $max_withdrawal_usd) {
        echo "<script>alert('The minimum withdrawal amount is $min_withdrawal_usd USD (".number_format($min_withdrawal_rwf)." RWF) and maximum is $max_withdrawal_usd USD (".number_format($max_withdrawal_rwf)." RWF).'); window.location.href = 'withdrawal.php';</script>";
        exit;
    }

    // Check bank details
    $bank_check_query = "SELECT * FROM user_banks WHERE user_id = $user_id";
    $bank_check_result = mysqli_query($conn, $bank_check_query);
    if (mysqli_num_rows($bank_check_result) == 0) {
        echo "<script>alert('Please add your bank details before making a withdrawal.'); window.location.href = 'binding_bank.php';</script>";
        exit;
    }

    // Check balance (including 5% fee)
    $total_deduction_usd = $withdraw_amount_usd * (1 + ($withdrawal_fee_percent / 100));
    if ($balance_usd < $total_deduction_usd) {
        echo "<script>alert('Insufficient balance. Remember there is a $withdrawal_fee_percent% fee.'); window.location.href = 'withdrawal.php';</script>";
        exit;
    }

    // Check withdrawal time
    if (!(($current_time >= $allowed_withdrawal_time_start && $current_time < '23:59') ||
        ($current_time >= '00:00' && $current_time <= $allowed_withdrawal_time_end))) {
        echo "<script>alert('Withdrawals are only allowed between 7:00 AM and 12:00 AM.'); window.location.href = 'withdrawal.php';</script>";
        exit;
    }

    // Check weekly withdrawal limit
    $weekly_withdrawal_query = "
        SELECT SUM(amount_usd) as total FROM withdrawals 
        WHERE client_id = $user_id 
        AND status = 'completed' 
        AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ";
    $weekly_result = mysqli_query($conn, $weekly_withdrawal_query);
    $weekly_row = mysqli_fetch_assoc($weekly_result);
    $weekly_total = $weekly_row['total'] ?? 0;
    
    if ($weekly_total + $withdraw_amount_usd > $max_withdrawal_usd) {
        echo "<script>alert('You have reached your weekly withdrawal limit of $max_withdrawal_usd USD.'); window.location.href = 'withdrawal.php';</script>";
        exit;
    }

    // Verify selected agent exists and is active
    $agent_verify_query = "SELECT id, phone_number FROM users WHERE id = $agent_id AND role = 'agent' AND status = 'active'";
    $agent_verify_result = mysqli_query($conn, $agent_verify_query);
    if (mysqli_num_rows($agent_verify_result) == 0) {
        echo "<script>alert('Invalid agent selected. Please choose a valid agent.'); window.location.href = 'withdrawal.php';</script>";
        exit;
    }
    
    $agent_info = mysqli_fetch_assoc($agent_verify_result);
    $agent_phone = $agent_info['phone_number'];

    // Calculate fee and update balance
    $fee_amount_usd = $withdraw_amount_usd * ($withdrawal_fee_percent / 100);
    $new_balance_usd = $balance_usd - $withdraw_amount_usd - $fee_amount_usd;
    
    $update_balance_query = "UPDATE users SET balance = $new_balance_usd WHERE id = $user_id";
    mysqli_query($conn, $update_balance_query);

    // Record transactions
    $log_withdrawal_query = "
        INSERT INTO transactions (client_id, amount, transaction_type, date) 
        VALUES ($user_id, $withdraw_amount_usd, 'withdrawal', NOW())
    ";
    mysqli_query($conn, $log_withdrawal_query);

    $log_fee_query = "
        INSERT INTO transactions (client_id, amount, transaction_type, date) 
        VALUES ($user_id, $fee_amount_usd, 'withdrawal_fee', NOW())
    ";
    mysqli_query($conn, $log_fee_query);

    // Record withdrawal
    $net_withdrawal = $withdraw_amount_rwf * (1 - $withdrawal_fee_percent / 100);
    $withdrawal_query = "
        INSERT INTO withdrawals (client_id, agent_id, amount_usd, amount_rwf, net_withdrawal, fee_percent, fee_amount, transaction_type, status, date) 
        VALUES ($user_id, $agent_id, $withdraw_amount_usd, $withdraw_amount_rwf, $net_withdrawal, $withdrawal_fee_percent, $fee_amount_usd, 'withdrawal', 'pending', NOW())
    ";
    mysqli_query($conn, $withdrawal_query);

    // Notification (simplified - in production you'd use a proper notification system)
    $subject = "Withdrawal Approval Request";
    $message = "User ID: $user_id has requested a withdrawal of $withdraw_amount_usd USD ($withdraw_amount_rwf RWF). Please approve.";
    // mail($agent_email, $subject, $message);

    echo "<script>alert('Withdrawal request submitted! You will receive $withdraw_amount_rwf_after_fee RWF (after $withdrawal_fee_percent% fee).'); window.location.href = 'withdrawal.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Funds | Recycling Paper Arts</title>
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

        .withdrawal-container {
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

        .withdrawal-container:hover {
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

        .info-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border-left: 4px solid var(--primary);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .agent-card {
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .agent-card:hover {
            border-color: var(--primary);
            background: rgba(16, 185, 129, 0.1);
        }

        .agent-card.selected {
            border-color: var(--primary);
            background: rgba(16, 185, 129, 0.15);
        }

        .agent-rating {
            color: var(--accent);
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

        .table {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 12px;
            overflow: hidden;
        }

        .table th {
            background: rgba(16, 185, 129, 0.1) !important;
            color: var(--primary) !important;
            border: none !important;
            font-weight: 600;
        }

        .table td {
            border: none !important;
            padding: 16px !important;
        }

        .table tr:nth-child(even) {
            background: rgba(16, 185, 129, 0.05);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .amount-converter {
                flex-direction: column;
            }
            
            .withdrawal-container {
                padding: 24px;
                margin: 24px 16px;
            }
            
            .exchange-rate {
                padding: 12px;
            }
        }

        @media (max-width: 576px) {
            .withdrawal-container {
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
            <h1 class="section-title"><i class="fas fa-money-bill-wave me-3"></i> Withdraw Funds</h1>
            <p class="lead text-muted">Request to withdraw your earnings securely</p>
        </div>

        <div class="withdrawal-container">
            <h2 class="mb-4"><i class="fas fa-money-bill-wave me-2 text-primary"></i> Withdraw Funds</h2>
            
            <div class="exchange-rate">
                <i class="fas fa-exchange-alt me-2"></i> Current Exchange Rate: 1 USD = <?php echo number_format($exchange_rate, 2); ?> RWF
            </div>
            
            <div class="info-card">
                <h5 class="mb-3"><i class="fas fa-info-circle me-2 text-primary"></i> Important Information</h5>
                <ul class="mb-0">
                    <li>Minimum withdrawal: <?php echo $min_withdrawal_usd; ?> USD (<?php echo number_format($min_withdrawal_rwf); ?> RWF)</li>
                    <li>Maximum withdrawal: <?php echo $max_withdrawal_usd; ?> USD (<?php echo number_format($max_withdrawal_rwf); ?> RWF) per week</li>
                    <li>Maximum of <b>1 withdrawal per day</b></li>
                    <li>Withdrawal fee: <b><?php echo $withdrawal_fee_percent; ?>%</b> of amount</li>
                    <li>Allowed time: 7:00 AM to 12:00 AM (Monday to Saturday)</li>
                    <li>Current Kigali time: <span id="kigali-time"></span></li>
                </ul>
            </div>
            
            <h4 class="mb-4">Your Current Balance: 
                <strong class="text-primary"><?php echo number_format($balance_usd, 2); ?> USD</strong> 
                (≈ <?php echo number_format($balance_rwf); ?> RWF)
            </h4>

            <form action="" method="POST" id="withdrawalForm">
                <div class="mb-4">
                    <label for="withdraw_amount_usd" class="form-label"><i class="fas fa-dollar-sign me-1"></i> Amount to Withdraw (USD)</label>
                    <input type="number" class="form-control" id="withdraw_amount_usd" name="withdraw_amount_usd" 
                           required min="<?php echo $min_withdrawal_usd; ?>" max="<?php echo $max_withdrawal_usd; ?>" step="0.01">
                </div>
                
                <div class="mb-4">
                    <div class="amount-converter">
                        <div class="form-group flex-grow-1">
                            <label><i class="fas fa-calculator me-1"></i> Equivalent in RWF</label>
                            <input type="text" class="form-control" id="withdraw_amount_rwf" readonly>
                        </div>
                        <div class="form-group flex-grow-1">
                            <label><i class="fas fa-percentage me-1"></i> Fee (<?php echo $withdrawal_fee_percent; ?>%)</label>
                            <input type="text" class="form-control" id="withdrawal_fee" readonly>
                        </div>
                        <div class="form-group flex-grow-1">
                            <label><i class="fas fa-hand-holding-usd me-1"></i> You'll Receive</label>
                            <input type="text" name="net_withdrawal" class="form-control" id="net_withdrawal" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label"><i class="fas fa-user-tie me-1"></i> Select an Agent</label>
                    <div id="agent-selection">
                        <?php if (count($agents) > 0): ?>
                            <?php foreach ($agents as $agent): ?>
                                <div class="agent-card" data-agent-id="<?php echo $agent['id']; ?>">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="agent_id" 
                                               id="agent_<?php echo $agent['id']; ?>" value="<?php echo $agent['id']; ?>" required>
                                        <label class="form-check-label" for="agent_<?php echo $agent['id']; ?>">
                                            <strong><?php echo htmlspecialchars($agent['fname']); ?></strong>
                                            <div class="agent-rating mt-1">
                                                <?php 
                                                $rating = $agent['rating'] ?? 5;
                                                for ($i = 1; $i <= 5; $i++): 
                                                    if ($i <= $rating): ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star"></i>
                                                    <?php endif; 
                                                endfor; ?>
                                            </div>
                                            <div class="text-muted mt-1"><i class="fas fa-phone me-1"></i> Phone: <?php echo htmlspecialchars($agent['phone_number']); ?></div>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i> No agents available at the moment. Please try again later.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn-magazine" <?php echo (count($agents) == 0) ? 'disabled' : ''; ?>>
                    <i class="fas fa-paper-plane me-2"></i> Request Withdrawal
                </button>
            </form>

            <h3 class="mt-5 mb-4"><i class="fas fa-history me-2 text-primary"></i> Withdrawal History</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount (USD)</th>
                            <th>Amount (RWF)</th>
                            <th>Fee</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $withdrawals_query = "
                            SELECT date, amount_usd, amount_rwf, fee_amount, status 
                            FROM withdrawals 
                            WHERE client_id = $user_id 
                            ORDER BY date DESC
                            LIMIT 10
                        ";
                        $withdrawals_result = mysqli_query($conn, $withdrawals_query);

                        if (mysqli_num_rows($withdrawals_result) > 0) {
                            while ($withdrawal = mysqli_fetch_assoc($withdrawals_result)) {
                                echo "<tr>
                                    <td>".date('M d, Y H:i', strtotime($withdrawal['date']))."</td>
                                    <td>".number_format($withdrawal['amount_usd'], 2)."</td>
                                    <td>".number_format($withdrawal['amount_rwf'])."</td>
                                    <td>".number_format($withdrawal['fee_amount'], 2)."</td>
                                    <td><span class='badge bg-".($withdrawal['status'] == 'completed' ? 'success' : ($withdrawal['status'] == 'pending' ? 'warning' : 'danger'))."'>".ucfirst($withdrawal['status'])."</span></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-4'><i class='fas fa-history fa-2x text-muted mb-3'></i><p class='text-muted'>No withdrawal history found</p></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
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
            // Calculate RWF equivalent and fees
            $('#withdraw_amount_usd').on('input', function() {
                const usdAmount = parseFloat($(this).val()) || 0;
                const exchangeRate = <?php echo json_encode($exchange_rate); ?>;
                const feePercent = <?php echo json_encode($withdrawal_fee_percent); ?>;
                
                const rwfAmount = usdAmount * exchangeRate;
                const feeAmount = usdAmount * (feePercent / 100);
                const netAmount = rwfAmount - (feeAmount * exchangeRate);
                
                $('#withdraw_amount_rwf').val(rwfAmount.toFixed(0) + ' RWF');
                $('#withdrawal_fee').val(feeAmount.toFixed(2) + ' USD (' + (feeAmount * exchangeRate).toFixed(0) + ' RWF)');
                $('#net_withdrawal').val(netAmount.toFixed(0) + ' RWF');
            });

            // Form validation
            $('#withdrawalForm').submit(function() {
                const usdAmount = parseFloat($('#withdraw_amount_usd').val());
                const minAmount = <?php echo json_encode($min_withdrawal_usd); ?>;
                const maxAmount = <?php echo json_encode($max_withdrawal_usd); ?>;
                
                if (usdAmount < minAmount || usdAmount > maxAmount) {
                    alert(`Amount must be between ${minAmount} USD and ${maxAmount} USD`);
                    return false;
                }
                
                const agentSelected = $('input[name="agent_id"]:checked').length > 0;
                if (!agentSelected) {
                    alert('Please select an agent for your withdrawal');
                    return false;
                }
                
                return true;
            });

            // Agent selection styling
            $('.agent-card').click(function() {
                $('.agent-card').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);
            });

            // Display current time in Kigali
            function updateKigaliTime() {
                const options = { timeZone: 'Africa/Kigali', hour: '2-digit', minute: '2-digit', second: '2-digit' };
                const formatter = new Intl.DateTimeFormat('en-US', options);
                document.getElementById('kigali-time').textContent = formatter.format(new Date());
            }
            setInterval(updateKigaliTime, 1000);
            updateKigaliTime();

            // Create particles
            createParticles();
        });
    </script>
</body>
</html>