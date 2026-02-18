<?php 
// Start the session to access user authentication data
session_start();

require_once '../../includes/db.php';

if (!isset($_GET['id'])) {
    die("Product ID not specified.");
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to make an investment.");
}

$productId = (int)$_GET['id'];
$product = $pdo->query("SELECT * FROM products_compound WHERE id = $productId")->fetch();

if (!$product) {
    die("Product not found.");
}

// Get user's current balance
$userId = $_SESSION['user_id'];
$balanceQuery = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$balanceQuery->execute([$userId]);
$userBalance = $balanceQuery->fetchColumn();

if ($userBalance === false) {
    die("User not found.");
}

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)$_POST['amount'];
    
    // Validate minimum investment
    if ($amount < $product['min_investment']) {
        $errorMessage = "Amount below minimum investment of $" . number_format($product['min_investment'], 2);
    }
    // Check if user has sufficient balance
    elseif ($amount > $userBalance) {
        $errorMessage = "Insufficient balance. Your current balance is $" . number_format($userBalance, 2);
    }
    else {
        // Calculate maturity date
        $maturityDate = date('Y-m-d H:i:s', strtotime("+{$product['cycle']} {$product['cycle_unit']}"));
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Insert investment
            $stmt = $pdo->prepare("
                INSERT INTO user_investments (
                    user_id, product_id, amount, maturity_date
                ) VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $productId, $amount, $maturityDate]);
            
            // Deduct amount from user balance
            $updateBalance = $pdo->prepare("
                UPDATE users 
                SET balance = balance - ? 
                WHERE id = ?
            ");
            $updateBalance->execute([$amount, $userId]);
            
            // Update product stats
            $updateProduct = $pdo->prepare("
                UPDATE products_compound 
                SET total_investors = total_investors + 1,
                    total_invested = total_invested + ? 
                WHERE id = ?
            ");
            $updateProduct->execute([$amount, $productId]);
            
            $transaction_query = "INSERT INTO transactions (client_id, transaction_type, amount, date) VALUES (?, 'investment', ?, NOW())";
        $stmt = $conn->prepare($transaction_query);
        $stmt->bind_param("id", $userId, $amount);
        $stmt->execute();
            // Commit transaction
            $pdo->commit();
            
            // Update user balance for display
            $userBalance -= $amount;
            
            $successMessage = "Investment successful! Amount: $" . number_format($amount, 2) . " | Maturity Date: $maturityDate";
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollback();
            $errorMessage = "Investment failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invest in <?= htmlspecialchars($product['name']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 2.2em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .product-info {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .product-info h2 {
            font-size: 1.5em;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .product-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            backdrop-filter: blur(5px);
        }

        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 1.2em;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 1.1em;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1.1em;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .investment-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #667eea;
        }

        .investment-info h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.3em;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
            font-weight: 500;
        }

        .info-value {
            color: #333;
            font-weight: 600;
        }

        .balance-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(79, 172, 254, 0.3);
        }

        .balance-card h3 {
            font-size: 1.1em;
            margin-bottom: 10px;
            opacity: 0.9;
            font-weight: 500;
        }

        .balance-amount {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .balance-sufficient {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
        }

        .balance-insufficient {
            background: linear-gradient(135deg, #ff6b6b 0%, #ffa8a8 100%);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .amount-validation {
            font-size: 0.9em;
            margin-top: 8px;
            padding: 8px;
            border-radius: 5px;
            display: none;
        }

        .validation-success {
            background: #d4edda;
            color: #155724;
            display: block;
        }

        .security-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9em;
            color: #856404;
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .submit-btn:disabled:hover {
            transform: none;
            box-shadow: none;
        }

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 1.8em;
            }
            
            .product-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💎 Investment Opportunity</h1>
        </div>

        <!-- User Balance Card -->
        <div class="balance-card <?= $userBalance >= $product['min_investment'] ? 'balance-sufficient' : 'balance-insufficient' ?>">
            <h3>💰 Your Available Balance</h3>
            <div class="balance-amount">$<?= number_format($userBalance, 2) ?></div>
            <?php if ($userBalance >= $product['min_investment']): ?>
                <div>✅ Sufficient for investment</div>
            <?php else: ?>
                <div>⚠️ Insufficient for minimum investment</div>
            <?php endif; ?>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                ❌ <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <div class="product-info">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <div class="product-stats">
                <div class="stat-card">
                    <div class="stat-label">Min Investment</div>
                    <div class="stat-value">$<?= number_format($product['min_investment'], 2) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Cycle</div>
                    <div class="stat-value"><?= $product['cycle'] ?> <?= $product['cycle_unit'] ?></div>
                </div>
                <?php if (isset($product['total_investors'])): ?>
                <div class="stat-card">
                    <div class="stat-label">Investors</div>
                    <div class="stat-value"><?= number_format($product['total_investors']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (isset($product['total_invested'])): ?>
                <div class="stat-card">
                    <div class="stat-label">Total Invested</div>
                    <div class="stat-value">$<?= number_format($product['total_invested'], 2) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="investment-info">
            <h3>Investment Details</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Product:</span>
                    <span class="info-value"><?= htmlspecialchars($product['name']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Minimum Amount:</span>
                    <span class="info-value">$<?= number_format($product['min_investment'], 2) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Investment Period:</span>
                    <span class="info-value"><?= $product['cycle'] ?> <?= $product['cycle_unit'] ?></span>
                </div>
                <?php if (isset($product['rate'])): ?>
                <div class="info-item">
                    <span class="info-label">Return Rate:</span>
                    <span class="info-value"><?= $product['rate'] ?>%</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <form method="POST" id="investmentForm">
            <div class="form-group">
                <label for="amount">💰 Investment Amount</label>
                <input 
                    type="number" 
                    id="amount"
                    name="amount" 
                    min="<?= $product['min_investment'] ?>" 
                    max="<?= $userBalance ?>"
                    step="0.01" 
                    required
                    placeholder="Enter amount ($<?= number_format($product['min_investment'], 2) ?> minimum)"
                    <?= $userBalance < $product['min_investment'] ? 'disabled' : '' ?>
                >
                <div id="amountValidation" class="amount-validation"></div>
            </div>
            
            <button type="submit" class="submit-btn" <?= $userBalance < $product['min_investment'] ? 'disabled' : '' ?>>
                🚀 Confirm Investment
            </button>
            <br>
            <br>
            <a href="../client_dashboard.php" class="submit-btn">Back To Dashboard</a>
        </form>

        <div class="security-note">
            <strong>⚠️ Security Notice:</strong> This form is for demonstration purposes. In production, ensure proper authentication, input validation, and SQL injection protection.
        </div>
    </div>

    <script>
        // Enhanced validation with balance checking
        const amountInput = document.getElementById('amount');
        const validation = document.getElementById('amountValidation');
        const submitBtn = document.querySelector('.submit-btn');
        const userBalance = <?= $userBalance ?>;
        const minInvestment = <?= $product['min_investment'] ?>;

        function validateAmount(value) {
            const amount = parseFloat(value);
            
            if (isNaN(amount) || amount <= 0) {
                return { valid: false, message: 'Please enter a valid amount' };
            }
            
            if (amount < minInvestment) {
                return { 
                    valid: false, 
                    message: `Amount must be at least ${minInvestment.toLocaleString()}` 
                };
            }
            
            if (amount > userBalance) {
                return { 
                    valid: false, 
                    message: `Insufficient balance. Available: ${userBalance.toLocaleString()}` 
                };
            }
            
            return { 
                valid: true, 
                message: `✅ Valid amount. Remaining balance: ${(userBalance - amount).toLocaleString()}` 
            };
        }

        amountInput.addEventListener('input', function(e) {
            const value = e.target.value;
            const result = validateAmount(value);
            
            // Update validation message
            validation.textContent = result.message;
            validation.className = `amount-validation ${result.valid ? 'validation-success' : 'validation-error'}`;
            
            // Update input styling
            if (result.valid) {
                e.target.style.borderColor = '#51cf66';
                e.target.style.boxShadow = '0 0 0 3px rgba(81, 207, 102, 0.1)';
                submitBtn.disabled = false;
            } else {
                e.target.style.borderColor = '#ff6b6b';
                e.target.style.boxShadow = '0 0 0 3px rgba(255, 107, 107, 0.1)';
                submitBtn.disabled = true;
            }
        });

        // Form submission with final validation
        document.getElementById('investmentForm').addEventListener('submit', function(e) {
            const amount = parseFloat(amountInput.value);
            const result = validateAmount(amount);
            
            if (!result.valid) {
                e.preventDefault();
                alert(result.message);
                return;
            }
            
            // Confirm investment
            const confirmMessage = `Are you sure you want to invest ${amount.toLocaleString()}?\n\nThis will leave you with ${(userBalance - amount).toLocaleString()} in your account.`;
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return;
            }
            
            // Show processing state
            submitBtn.innerHTML = '⏳ Processing Investment...';
            submitBtn.disabled = true;
        });

        // Initialize validation on page load
        if (amountInput.value) {
            amountInput.dispatchEvent(new Event('input'));
        }
    </script>
</body>
</html>