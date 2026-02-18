<?php
// session_start();

// Check if the user is logged in and has a 'client' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's ID
$clientId = $_SESSION['user_id'];

require_once '../includes/db.php';

// Fetch user balance
$user_query = "SELECT balance FROM users WHERE id = '$clientId'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
$user_balance = $user['balance'];

// Fetch compound products
$stmt = $pdo->query("SELECT * FROM products_compound WHERE status = 'active'");
$compound_products = $stmt->fetchAll();

?>
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --dark-bg: #1a1a2e;
        --card-bg: rgba(255, 255, 255, 0.95);
        --text-primary: #2d3748;
        --text-secondary: #718096;
        --success-color: #48bb78;
        --warning-color: #ed8936;
        --danger-color: #f56565;
        --border-radius: 20px;
        --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
        --shadow-hover: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .floating-shapes {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .shape {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        animation: float 6s ease-in-out infinite;
    }

    .shape:nth-child(1) {
        width: 80px;
        height: 80px;
        top: 20%;
        left: 20%;
        animation-delay: 0s;
    }

    .shape:nth-child(2) {
        width: 120px;
        height: 120px;
        top: 60%;
        right: 20%;
        animation-delay: 2s;
    }

    .shape:nth-child(3) {
        width: 60px;
        height: 60px;
        bottom: 20%;
        left: 60%;
        animation-delay: 4s;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    /* Main Content */
    .main-content {
        margin-left: 0;
        padding: 20px;
        transition: margin-left 0.3s ease;
        position: relative;
        z-index: 10;
    }

    .main-content.shifted {
        margin-left: 280px;
    }

    /* Header */
    .header {
        border-radius: var(--border-radius);
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: var(--shadow-soft);
        backdrop-filter: blur(10px);
    }

    .logo-container {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .logo {
        width: 80px;
        height: 80px;
        background: var(--secondary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: white;
        box-shadow: var(--shadow-soft);
    }

    .company-info h1 {
        font-size: 32px;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 5px;
    }

    .company-info p {
        color: var(--text-secondary);
        font-size: 16px;
    }

    /* Page Title */
    .page-title {
        text-align: center;
        margin-bottom: 50px;
        color: white;
    }

    .page-title h2 {
        font-size: 36px;
        font-weight: 800;
        margin-bottom: 15px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .page-title p {
        font-size: 18px;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Products Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
        margin-bottom: 100px;
    }

    .product-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow-soft);
        transition: all 0.3s ease;
        position: relative;
        backdrop-filter: blur(10px);
    }

    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-hover);
    }

    .product-header {
        padding: 30px;
        text-align: center;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .product-icon {
        width: 80px;
        height: 80px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: white;
        margin: 0 auto 20px;
        box-shadow: var(--shadow-soft);
    }

    .product-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 10px;
    }

    .product-body {
        padding: 30px;
    }

    .product-details {
        margin-bottom: 30px;
    }

    .detail-item {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: rgba(102, 126, 234, 0.05);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .detail-item:hover {
        background: rgba(102, 126, 234, 0.1);
        transform: translateX(5px);
    }

    .detail-icon {
        width: 50px;
        height: 50px;
        background: var(--accent-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: white;
        font-size: 18px;
    }

    .detail-content {
        flex: 1;
    }

    .detail-label {
        font-size: 14px;
        color: var(--text-secondary);
        margin-bottom: 5px;
    }

    .detail-value {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .profit-rate {
        background: var(--secondary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 20px;
    }

    .invest-button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 18px;
        background: var(--secondary-gradient);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-soft);
    }

    .invest-button:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
        color: white;
        text-decoration: none;
    }

    .invest-button i {
        font-size: 18px;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }

    /* Loading Animation */
    .main-content.loading {
        opacity: 0;
        animation: fadeIn 0.8s ease forwards;
    }

    @keyframes fadeIn {
        to { opacity: 1; }
    }

    /* Product image styles */
    .product-image-co-container {
        height: 200px;
        overflow: hidden;
        position: relative;
    }
    
    .product-image-co {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .product-card:hover .product-image-co {
        transform: scale(1.05);
    }
    
    .product-icon {
        position: absolute;
        bottom: -40px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
        border: 4px solid var(--card-bg);
    }
    
    .product-header {
        padding-top: 60px;
        position: relative;
        padding-bottom: 40px;
    }

    /* New styles for potential earnings */
    .potential-earnings {
        background: rgba(72, 187, 120, 0.1);
        border-radius: 12px;
        padding: 15px;
        margin-top: 20px;
        border-left: 4px solid var(--success-color);
    }
    
    .potential-earnings-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .potential-earnings-title i {
        color: var(--success-color);
    }
    
    .potential-earnings-value {
        font-size: 18px;
        font-weight: 700;
        color: var(--success-color);
        margin-bottom: 5px;
    }
    
    .potential-earnings-note {
        font-size: 12px;
        color: var(--text-secondary);
        font-style: italic;
    }
    
    /* Badge for featured products */
    .featured-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: var(--secondary-gradient);
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        z-index: 2;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .main-content {
            padding: 15px;
        }
        
        .header {
            padding: 20px;
        }
        
        .logo-container {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }
        
        .company-info h1 {
            font-size: 24px;
        }
        
        .page-title h2 {
            font-size: 28px;
        }
    }

    @media (max-width: 576px) {

        .main-content.shifted {
            margin-left: 250px;
        }
        
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .product-card {
            margin: 0 10px;
        }
    }
</style>
</head>
<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Main Content -->
    <div class="main-content loading" id="mainContent">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="company-info">
                    <h1>LongTerm Investments</h1>
                    <p>Premium Global Investment Solutions</p>
                </div>
            </div>
        </div>

        <!-- Page Title -->
        <div class="page-title">
            <h2>Compound Investment Products</h2>
            <p>Discover our carefully curated selection of high-yield investment opportunities designed for long-term wealth building and portfolio diversification.</p>
        </div>

        <!-- Products Grid -->
        <div class="products-grid">
            <?php foreach ($compound_products as $product): 
                // Calculate potential earnings
                $min_investment = $product['min_investment'];
                $profit_rate = $product['profit_rate'] / 100;
                
                // Calculate earnings based on cycle
                if ($product['cycle_unit'] == 'days') {
                    $cycles_per_year = 365 / $product['cycle'];
                } elseif ($product['cycle_unit'] == 'months') {
                    $cycles_per_year = 12 / $product['cycle'];
                } else { // years
                    $cycles_per_year = 1 / $product['cycle'];
                }
                
                $annual_earnings = ($min_investment * $profit_rate) / 100;
                $total_earnings = ($annual_earnings * $cycles_per_year) + $product['min_investment'];
            ?>
                <div class="product-card">
                    <?php if ($product['featured']): ?>
                        <span class="featured-badge">Featured</span>
                    <?php endif; ?>
                    
                    <!-- Product Image Section -->
                    <div class="product-image-co-container">
                        <?php if (!empty($product['image'])): ?>
                            <img src="../images/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image-co">
                        <?php else: ?>
                            <img src="../assets/images/default-product.jpg" alt="Default product image" class="product-image-co">
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-header">
                        <div class="product-icon">
                            <i class="fas fa-<?= $product['icon'] ?? 'chart-line' ?>"></i>
                        </div>
                        <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                    </div>
                    
                    <div class="product-body">
                        <div class="product-details">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Annual Profit Rate</div>
                                    <div class="detail-value profit-rate"><?= $product['profit_rate'] ?>%</div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Investment Cycle</div>
                                    <div class="detail-value"><?= $product['cycle'] ?> <?= ucfirst($product['cycle_unit']) ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Minimum Investment</div>
                                    <div class="detail-value">$<?= number_format($product['min_investment'], 2) ?></div>
                                </div>
                            </div>
                            
                            <!-- Potential Earnings Section -->
                            <div class="potential-earnings">
                                <div class="potential-earnings-title">
                                    <i class="fas fa-coins"></i>
                                    <span>Potential Earnings</span>
                                </div>
                                <div class="potential-earnings-value">
                                    $<?= number_format($total_earnings, 2) ?> per cycle
                                </div>
                                <div class="potential-earnings-note">
                                    Based on minimum investment of $<?= number_format($min_investment, 2) ?>
                                </div>
                            </div>
                        </div>
                        
                        <a href="comp/invest.php?id=<?= $product['id'] ?>" class="invest-button">
                            <i class="fas fa-rocket"></i>
                            Invest Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>