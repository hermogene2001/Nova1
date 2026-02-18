<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../includes/db.php';
// include '../calculate_daily_profit.php'; // Ensure this function is defined to calculate daily earnings

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$clientId = $_SESSION['user_id'];
$productId = $_GET['product_id'];

// Fetch product details, including price, cycle days, and daily earning
$product_query = "SELECT price, daily_earning, cycle FROM products WHERE id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();

if ($product) {
    $price = $product['price'];
    $cycle_days = $product['cycle'];
    $dailyEarning = $product['daily_earning'];
    
    // Check how many times this user has purchased this product
    $purchase_count_query = "SELECT COUNT(*) as purchase_count FROM purchases WHERE client_id = ? AND product_id = ?";
    $count_stmt = $conn->prepare($purchase_count_query);
    $count_stmt->bind_param("ii", $clientId, $productId);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_data = $count_result->fetch_assoc();
    $purchase_count = $count_data['purchase_count'];
    
    // Check if user has already purchased this product twice
    if ($purchase_count >= 2) {
        echo "You have already purchased this product the maximum number of times (2).";
        ?>
        <script type="text/javascript">
            setTimeout(function() {
                window.location.href = "../views/client_dashboard.php";
            }, 3000); // Redirect after 3 seconds
        </script>
        <?php
        exit;
    }
    
    // Fetch user balance
    $user_query = "SELECT balance FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $clientId);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_balance = $user['balance'];
    
    // Check if user has enough balance
    if ($user_balance >= $price) {
        // Deduct price from user balance
        $new_balance = $user_balance - $price;
        $update_balance_stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
        $update_balance_stmt->bind_param("di", $new_balance, $clientId);
        $update_balance_stmt->execute();
        
        // Record transaction for the purchase
        $transaction_query = "INSERT INTO transactions (client_id, transaction_type, amount, date) VALUES (?, 'purchase', ?, NOW())";
        $transaction_stmt = $conn->prepare($transaction_query);
        $transaction_stmt->bind_param("id", $clientId, $price);
        $transaction_stmt->execute();
        
        // Record the purchase with start and end dates based on the cycle
        $purchase_datetime = date('Y-m-d');
        $end_datetime = date('Y-m-d H:i:s', strtotime("+$cycle_days days", strtotime($purchase_datetime)));
        
        $insert_purchase = "INSERT INTO purchases (client_id, product_id, purchase_date, end_datetime, last_earned) 
                            VALUES (?, ?, ?, ?, ?)";
        $purchase_stmt = $conn->prepare($insert_purchase);
        $purchase_stmt->bind_param("iisss", $clientId, $productId, $purchase_datetime, $end_datetime, $purchase_datetime);
        $purchase_stmt->execute();
        
        // Insert into investments table to track daily earnings and end date
        $investment_query = "INSERT INTO investments (user_id, amount, invested_at, start_date, end_date, status, daily_profit, last_profit_update) 
                     VALUES (?, ?, NOW(), ?, ?, 'active', '0.00', ?)";
        $investment_stmt = $conn->prepare($investment_query);
        $investment_stmt->bind_param("idsss", $clientId, $price, $purchase_datetime, $end_datetime, $purchase_datetime);
        $investment_stmt->execute();
        
        // Display success message
        $remaining_purchases = 2 - ($purchase_count + 1);
        echo "Purchase and investment successful!<br>";
        echo "Your investment end date is: " . $end_datetime . "<br>";
        echo "You can purchase this product " . $remaining_purchases . " more time(s).";
?>
<script type="text/javascript">
    setTimeout(function() {
        window.location.href = "../views/purchased.php";
    }, 5000); // Redirect after 5 seconds
</script>
<?php
        exit;
    } else {
        echo "Insufficient balance.";
        ?>
        <script type="text/javascript">
            setTimeout(function() {
                window.location.href = "../views/client_dashboard.php";
            }, 3000); // Redirect after 3 seconds
        </script>
        <?php
        exit;
    }
} else {
    echo "Product not found.";
    ?>
    <script type="text/javascript">
        setTimeout(function() {
            window.location.href = "../views/client_dashboard.php";
        }, 3000); // Redirect after 3 seconds
    </script>
    <?php
    exit;
}

// Check and update investments when the end date has passed
$today = date('Y-m-d');
$update_query = "UPDATE investments SET status = 'completed' WHERE end_date < ? AND status = 'active'";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("s", $today);
$update_stmt->execute();
echo "Investment statuses updated successfully.";
?>