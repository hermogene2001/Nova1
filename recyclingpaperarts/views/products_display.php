<?php
// Check if tables and columns exist
function tableExists($conn, $tableName) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$tableName'");
    return mysqli_num_rows($result) > 0;
}

function columnExists($conn, $tableName, $columnName) {
    if (!tableExists($conn, $tableName)) {
        return false;
    }
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'");
    return mysqli_num_rows($result) > 0;
}

// Build query based on existing tables and columns
$investments_table = '0';
if (tableExists($conn, 'investments')) {
    if (columnExists($conn, 'investments', 'product_id')) {
        $investments_table = '(SELECT COUNT(*) FROM investments WHERE product_id = products.id)';
    } elseif (columnExists($conn, 'investments', 'product')) {
        $investments_table = '(SELECT COUNT(*) FROM investments WHERE product = products.id)';
    } elseif (columnExists($conn, 'investments', 'product_name')) {
        $investments_table = '(SELECT COUNT(*) FROM investments WHERE product_name = products.name)';
    }
}

$product_reviews_table = '0';
if (tableExists($conn, 'product_reviews')) {
    if (columnExists($conn, 'product_reviews', 'product_id')) {
        $product_reviews_table = '(SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id)';
    } elseif (columnExists($conn, 'product_reviews', 'product')) {
        $product_reviews_table = '(SELECT AVG(rating) FROM product_reviews WHERE product = products.id)';
    }
}

$products_query = "SELECT *, 
    $investments_table as investor_count,
    $product_reviews_table as avg_rating
    FROM products WHERE status = 'active' ORDER BY id DESC";

$products_result = mysqli_query($conn, $products_query);

// Count products
$product_count = mysqli_num_rows($products_result);

// Prepare products array for JavaScript filtering
$products_array = [];
mysqli_data_seek($products_result, 0);
while($product = mysqli_fetch_assoc($products_result)) {
    $total_return = $product['price'] + ($product['daily_earning'] * $product['cycle']);
    $roi_percentage = (($total_return - $product['price']) / $product['price']) * 100;
    $product['total_return'] = $total_return;
    $product['roi_percentage'] = $roi_percentage;
    $product['investor_count'] = $product['investor_count'] ?: 0;
    $product['avg_rating'] = $product['avg_rating'] ?: 0;
    $products_array[] = $product;
}
?>

<style>
    .products-section {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 25px;
        padding: 40px;
        margin: 30px 0;
        box-shadow: 0 15px 50px rgba(0,0,0,0.12);
        overflow: hidden;
        position: relative;
        border: 1px solid rgba(79, 172, 254, 0.1);
    }
    
    .products-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #4facfe 0%, #00f2fe 50%, #4facfe 100%);
        border-radius: 25px 25px 0 0;
    }
    
    .section-title {
        text-align: center;
        margin-bottom: 35px;
        color: #1e293b;
        font-size: 32px;
        font-weight: 800;
        position: relative;
        padding-bottom: 15px;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
        border-radius: 2px;
    }
    
    .filters-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 35px;
        padding: 25px;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-radius: 20px;
        align-items: center;
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
        border: 1px solid rgba(79, 172, 254, 0.15);
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 200px;
    }
    
    .filter-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .filter-select {
        padding: 12px 15px;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        background: white;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .filter-select:focus {
        outline: none;
        border-color: #4facfe;
        box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
    }
    
    .search-box {
        flex: 2;
        min-width: 250px;
    }
    
    .search-input {
        width: 100%;
        padding: 12px 15px 12px 40px;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        background: white;
        font-size: 14px;
        transition: all 0.3s ease;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 12px center;
    }
    
    .search-input:focus {
        outline: none;
        border-color: #4facfe;
        box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
    }
    
    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 16px;
        transition: color 0.3s ease;
    }
    
    .search-box {
        position: relative;
    }
    
    .search-input:focus + .search-icon,
    .search-input:not(:placeholder-shown) + .search-icon {
        color: #4facfe;
    }
    
    .tab-container {
        margin-bottom: 30px;
    }
    
    .tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0;
    }
    
    .tab {
        padding: 12px 25px;
        background: transparent;
        border: none;
        border-radius: 10px 10px 0 0;
        font-weight: 600;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .tab.active {
        color: #4facfe;
        background: linear-gradient(135deg, rgba(79, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.1) 100%);
    }
    
    .tab:hover {
        color: #4facfe;
        background: rgba(79, 172, 254, 0.05);
    }
    
    .tab.active:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
        border-radius: 2px 2px 0 0;
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        transition: all 0.3s ease;
    }
    
    .product-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(79, 172, 254, 0.1);
        position: relative;
        transform: translateY(0);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .product-card:hover {
        transform: translateY(-12px) scale(1.03);
        box-shadow: 0 25px 50px rgba(79, 172, 254, 0.2);
        border-color: rgba(79, 172, 254, 0.3);
    }
    
    .product-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
        transform-origin: left;
    }
    
    .product-card:hover::before {
        transform: scaleX(1);
    }
    
    .product-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        z-index: 2;
        box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
    }
    
    .product-image-container {
        position: relative;
        overflow: hidden;
        height: 200px;
    }
    
    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    
    .product-card:hover .product-image {
        transform: scale(1.1) rotate(1deg);
    }
    
    .product-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 60%);
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        align-items: flex-end;
        padding: 20px;
    }
    
    .product-card:hover .product-overlay {
        opacity: 1;
    }
    
    .quick-actions {
        display: flex;
        gap: 10px;
    }
    
    .action-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.9);
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .action-btn:hover {
        background: white;
        transform: translateY(-2px) scale(1.1);
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    
    .action-btn i {
        color: #4facfe;
        font-size: 16px;
    }
    
    .product-info {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .product-name {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 15px;
        line-height: 1.3;
        transition: color 0.3s ease;
    }
    
    .product-card:hover .product-name {
        color: #4facfe;
    }
    
    .product-description {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 20px;
        line-height: 1.5;
        flex-grow: 1;
    }
    
    .product-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }
    
    .tag {
        background: linear-gradient(135deg, rgba(79, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.1) 100%);
        color: #4facfe;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid rgba(79, 172, 254, 0.2);
    }
    
    .product-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .stat-item {
        text-align: center;
        padding: 15px;
        background: linear-gradient(135deg, rgba(79, 172, 254, 0.08) 0%, rgba(0, 242, 254, 0.08) 100%);
        border-radius: 12px;
        border: 1px solid rgba(79, 172, 254, 0.15);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    
    .stat-item:hover::before {
        transform: scaleX(1);
    }
    
    .stat-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(79, 172, 254, 0.15);
        border-color: rgba(79, 172, 254, 0.3);
    }
    
    .stat-value {
        font-size: 20px;
        font-weight: 800;
        color: #4facfe;
        margin-bottom: 5px;
        transition: all 0.3s ease;
    }
    
    .stat-label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    .social-proof {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding: 12px;
        background: rgba(255, 242, 204, 0.3);
        border-radius: 12px;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }
    
    .rating {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .stars {
        color: #ffc107;
        font-size: 14px;
    }
    
    .investors {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #64748b;
        font-size: 13px;
        font-weight: 500;
    }
    
    .investors i {
        color: #4facfe;
    }
    
    .product-details {
        margin-bottom: 20px;
        border-top: 1px solid #e9ecef;
        padding-top: 15px;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
    }
    
    .detail-label {
        color: #6c757d;
    }
    
    .detail-value {
        font-weight: 600;
        color: #2d3748;
    }
    
    .buy-button {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
        margin-top: auto;
    }
    
    .buy-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.6s ease;
    }
    
    .buy-button:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 10px 25px rgba(79, 172, 254, 0.5);
        letter-spacing: 1.5px;
    }
    
    .buy-button:hover::before {
        left: 100%;
    }
    
    .buy-button:active {
        transform: translateY(0) scale(0.98);
    }
    
    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .secondary-button {
        flex: 1;
        padding: 12px;
        background: white;
        color: #4facfe;
        border: 2px solid #4facfe;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .secondary-button:hover {
        background: #4facfe;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #718096;
    }
    
    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
        color: #a0aec0;
    }
    
    .sort-indicator {
        margin-left: 8px;
        color: #4facfe;
    }
    
    @media (max-width: 992px) {
        .filters-container {
            flex-direction: column;
        }
        
        .filter-group {
            width: 100%;
        }
        
        .tabs {
            overflow-x: auto;
            padding-bottom: 10px;
        }
    }
    
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .section-title {
            font-size: 24px;
        }
        
        .tab {
            padding: 10px 20px;
            font-size: 14px;
        }
        
        .product-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="products-section">
    <h2 class="section-title">
        <i class="fas fa-newspaper me-2"></i>Available Paper Art Kits
    </h2>
    
    <!-- Enhanced Filters Section -->
    <div class="filters-container">
        <div class="search-box">
            <input type="text" id="searchInput" class="search-input" placeholder="Search products by name, description, or tags...">
            <i class="fas fa-search search-icon"></i>
        </div>
        
        <div class="filter-group">
            <label class="filter-label">Sort By</label>
            <select id="sortSelect" class="filter-select">
                <option value="default">Default</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
                <option value="roi-high">ROI: High to Low</option>
                <option value="daily-earning">Daily Return</option>
                <option value="cycle-short">Short Cycle</option>
                <option value="cycle-long">Long Cycle</option>
                <option value="popularity">Most Popular</option>
                <option value="rating">Highest Rated</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label class="filter-label">Price Range</label>
            <select id="priceFilter" class="filter-select">
                <option value="all">All Prices</option>
                <option value="0-50">Under $50</option>
                <option value="50-100">$50 - $100</option>
                <option value="100-200">$100 - $200</option>
                <option value="200-500">$200 - $500</option>
                <option value="500+">$500+</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label class="filter-label">Investment Term</label>
            <select id="termFilter" class="filter-select">
                <option value="all">All Terms</option>
                <option value="short">Short Term (&lt; 30 days)</option>
                <option value="medium">Medium Term (30-90 days)</option>
                <option value="long">Long Term (&gt; 90 days)</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label class="filter-label">ROI Level</label>
            <select id="roiFilter" class="filter-select">
                <option value="all">All ROI Levels</option>
                <option value="low">Low ROI (&lt; 15%)</option>
                <option value="medium">Medium ROI (15-25%)</option>
                <option value="high">High ROI (&gt; 25%)</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label class="filter-label">&nbsp;</label>
            <button id="resetFilters" class="btn btn-outline-secondary w-100">
                <i class="fas fa-sync-alt me-2"></i>Reset Filters
            </button>
        </div>
    </div>
    
    <!-- Tabs Section -->
    <div class="tab-container">
        <div class="tabs">
            <button class="tab active" data-tab="all">All Products <span class="sort-indicator">(<?php echo $product_count; ?>)</span></button>
            <button class="tab" data-tab="premium">Premium <span class="sort-indicator">(<?php echo count(array_filter($products_array, function($p) { return $p['price'] > 100; })); ?>)</span></button>
            <button class="tab" data-tab="budget">Budget <span class="sort-indicator">(<?php echo count(array_filter($products_array, function($p) { return $p['price'] <= 100; })); ?>)</span></button>
            <button class="tab" data-tab="high-roi">High ROI <span class="sort-indicator">(<?php echo count(array_filter($products_array, function($p) { return $p['roi_percentage'] > 20; })); ?>)</span></button>
        </div>
    </div>
    
    <?php if ($product_count > 0): ?>
        <div class="products-grid" id="productsGrid">
            <!-- Products will be rendered by JavaScript -->
        </div>
        
        <!-- Loading spinner -->
        <div id="loadingSpinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading products...</p>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No Products Available</h3>
            <p>Check back later for new paper art kit releases!</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Product data from PHP
const products = <?php echo json_encode($products_array); ?>;
let currentTab = 'all';
let filteredProducts = [...products];

// DOM elements
const searchInput = document.getElementById('searchInput');
const sortSelect = document.getElementById('sortSelect');
const priceFilter = document.getElementById('priceFilter');
const termFilter = document.getElementById('termFilter');
const roiFilter = document.getElementById('roiFilter');
const resetFiltersBtn = document.getElementById('resetFilters');
const tabs = document.querySelectorAll('.tab');
const productsGrid = document.getElementById('productsGrid');

// Tab switching
tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentTab = tab.dataset.tab;
        filterAndRenderProducts();
    });
});

// Search functionality
searchInput.addEventListener('input', filterAndRenderProducts);

// Sort functionality
sortSelect.addEventListener('change', filterAndRenderProducts);

// Additional filters
priceFilter.addEventListener('change', filterAndRenderProducts);
termFilter.addEventListener('change', filterAndRenderProducts);
roiFilter.addEventListener('change', filterAndRenderProducts);

// Reset filters
resetFiltersBtn.addEventListener('click', () => {
    searchInput.value = '';
    sortSelect.value = 'default';
    priceFilter.value = 'all';
    termFilter.value = 'all';
    roiFilter.value = 'all';
    currentTab = 'all';
    tabs.forEach(t => t.classList.remove('active'));
    tabs[0].classList.add('active');
    filterAndRenderProducts();
});

// Filter and render products
function filterAndRenderProducts() {
    let filtered = [...products];
    
    // Apply tab filter
    if (currentTab === 'premium') {
        filtered = filtered.filter(p => p.price > 100);
    } else if (currentTab === 'budget') {
        filtered = filtered.filter(p => p.price <= 100);
    } else if (currentTab === 'high-roi') {
        filtered = filtered.filter(p => p.roi_percentage > 20);
    }
    
    // Apply search filter
    const searchTerm = searchInput.value.toLowerCase();
    if (searchTerm) {
        filtered = filtered.filter(p => 
            p.name.toLowerCase().includes(searchTerm)
        );
    }
    
    // Apply price filter
    const priceRange = priceFilter.value;
    if (priceRange !== 'all') {
        if (priceRange === '0-50') {
            filtered = filtered.filter(p => p.price <= 50);
        } else if (priceRange === '50-100') {
            filtered = filtered.filter(p => p.price > 50 && p.price <= 100);
        } else if (priceRange === '100-200') {
            filtered = filtered.filter(p => p.price > 100 && p.price <= 200);
        } else if (priceRange === '200-500') {
            filtered = filtered.filter(p => p.price > 200 && p.price <= 500);
        } else if (priceRange === '500+') {
            filtered = filtered.filter(p => p.price > 500);
        }
    }
    
    // Apply term filter
    const termValue = termFilter.value;
    if (termValue !== 'all') {
        if (termValue === 'short') {
            filtered = filtered.filter(p => p.cycle < 30);
        } else if (termValue === 'medium') {
            filtered = filtered.filter(p => p.cycle >= 30 && p.cycle <= 90);
        } else if (termValue === 'long') {
            filtered = filtered.filter(p => p.cycle > 90);
        }
    }
    
    // Apply ROI filter
    const roiValue = roiFilter.value;
    if (roiValue !== 'all') {
        if (roiValue === 'low') {
            filtered = filtered.filter(p => p.roi_percentage < 15);
        } else if (roiValue === 'medium') {
            filtered = filtered.filter(p => p.roi_percentage >= 15 && p.roi_percentage <= 25);
        } else if (roiValue === 'high') {
            filtered = filtered.filter(p => p.roi_percentage > 25);
        }
    }
    
    // Apply sorting
    const sortValue = sortSelect.value;
    if (sortValue === 'price-low') {
        filtered.sort((a, b) => a.price - b.price);
    } else if (sortValue === 'price-high') {
        filtered.sort((a, b) => b.price - a.price);
    } else if (sortValue === 'roi-high') {
        filtered.sort((a, b) => b.roi_percentage - a.roi_percentage);
    } else if (sortValue === 'daily-earning') {
        filtered.sort((a, b) => b.daily_earning - a.daily_earning);
    } else if (sortValue === 'cycle-short') {
        filtered.sort((a, b) => a.cycle - b.cycle);
    } else if (sortValue === 'cycle-long') {
        filtered.sort((a, b) => b.cycle - a.cycle);
    } else if (sortValue === 'popularity') {
        filtered.sort((a, b) => (b.investor_count || 0) - (a.investor_count || 0));
    } else if (sortValue === 'rating') {
        filtered.sort((a, b) => (b.avg_rating || 0) - (a.avg_rating || 0));
    }
    
    filteredProducts = filtered;
    renderProducts(filtered);
}

// Render products
function renderProducts(productsToRender) {
    if (productsToRender.length === 0) {
        productsGrid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="fas fa-search"></i>
                <h3>No Products Found</h3>
                <p>Try adjusting your filters or search terms</p>
            </div>
        `;
        return;
    }
    
    // Show loading spinner
    document.getElementById('loadingSpinner').style.display = 'block';
    productsGrid.style.opacity = '0.5';
    
    // Simulate loading delay for smooth transition
    setTimeout(() => {
        productsGrid.innerHTML = productsToRender.map(product => {
            // Generate star ratings
            const stars = generateStars(product.avg_rating || 0);
            
            // Generate tags based on product properties
            const tags = generateTags(product);
            
            return `
                <div class="product-card" data-id="${product.id}">
                    <div class="product-badge">${product.roi_percentage > 25 ? '🔥 HOT' : product.roi_percentage > 20 ? '⭐ POPULAR' : '✨ NEW'}</div>
                    
                    <div class="product-image-container">
                        <img src="../uploads/${product.image}" 
                             alt="${product.name}" 
                             class="product-image"
                             onerror="this.src='../assets/default-product.svg'">
                        <div class="product-overlay">
                            <div class="quick-actions">
                                <button class="action-btn" onclick="addToWishlist(${product.id})" title="Add to Wishlist">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <button class="action-btn" onclick="quickView(${product.id})" title="Quick View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn" onclick="compareProduct(${product.id})" title="Compare">
                                    <i class="fas fa-balance-scale"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name">${product.name}</h3>
                        
                        <p class="product-description">
                            ${product.description || getDefaultDescription(product)}
                        </p>
                        
                        <div class="product-tags">
                            ${tags}
                        </div>
                        
                        <div class="social-proof">
                            <div class="rating">
                                <div class="stars">${stars}</div>
                                <span class="rating-text">${parseFloat(product.avg_rating || 0).toFixed(1)}</span>
                            </div>
                            <div class="investors">
                                <i class="fas fa-users"></i>
                                <span>${product.investor_count || 0} investors</span>
                            </div>
                        </div>
                        
                        <div class="product-stats">
                            <div class="stat-item">
                                <div class="stat-value">$${parseFloat(product.price).toFixed(2)}</div>
                                <div class="stat-label">Investment</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">${parseFloat(product.roi_percentage).toFixed(1)}%</div>
                                <div class="stat-label">ROI</div>
                            </div>
                        </div>
                        
                        <div class="product-details">
                            <div class="detail-row">
                                <span class="detail-label">Daily Return</span>
                                <span class="detail-value text-success">$${parseFloat(product.daily_earning).toFixed(2)}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Investment Cycle</span>
                                <span class="detail-value">${product.cycle} days</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total Return</span>
                                <span class="detail-value text-primary">$${parseFloat(product.total_return).toFixed(2)}</span>
                            </div>
                        </div>
                        
                        <button class="buy-button" onclick="buyProduct(${product.id}, ${product.price})">
                            <i class="fas fa-shopping-cart me-2"></i>Invest Now
                        </button>
                        
                        <div class="button-group">
                            <button class="secondary-button" onclick="viewDetails(${product.id})">
                                <i class="fas fa-info-circle me-1"></i>Details
                            </button>
                            <button class="secondary-button" onclick="shareProduct(${product.id})">
                                <i class="fas fa-share-alt me-1"></i>Share
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Hide loading spinner
        document.getElementById('loadingSpinner').style.display = 'none';
        productsGrid.style.opacity = '1';
    }, 300);
}

// Helper functions
function generateStars(rating) {
    let stars = '';
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5;
    const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
    
    for(let i = 0; i < fullStars; i++) {
        stars += '<i class="fas fa-star"></i>';
    }
    if(halfStar) {
        stars += '<i class="fas fa-star-half-alt"></i>';
    }
    for(let i = 0; i < emptyStars; i++) {
        stars += '<i class="far fa-star"></i>';
    }
    return stars;
}

function generateTags(product) {
    const tags = [];
    
    if(product.roi_percentage > 25) tags.push('High ROI');
    if(product.cycle < 30) tags.push('Short Term');
    if(product.cycle > 90) tags.push('Long Term');
    if(product.price < 100) tags.push('Budget Friendly');
    if(product.price > 500) tags.push('Premium');
    
    return tags.map(tag => `<span class="tag">${tag}</span>`).join('');
}

function getDefaultDescription(product) {
    const descriptions = [
        `Invest in our premium ${product.name.toLowerCase()} and enjoy consistent daily returns of $${product.daily_earning.toFixed(2)} over ${product.cycle} days. Perfect for both beginners and experienced investors.`,
        `Our ${product.name.toLowerCase()} offers excellent value with a ${product.roi_percentage.toFixed(1)}% return on investment. Start earning passive income today with this reliable investment option.`,
        `Join hundreds of satisfied investors with our ${product.name.toLowerCase()}. This proven investment strategy delivers steady returns with minimal risk over ${product.cycle} days.`
    ];
    
    // Return a random description
    return descriptions[Math.floor(Math.random() * descriptions.length)];
}

// Interactive functions
function addToWishlist(productId) {
    // Implementation for adding to wishlist
    showToast('Added to wishlist!', 'success');
}

function quickView(productId) {
    // Implementation for quick view modal
    showToast('Quick view coming soon!', 'info');
}

function compareProduct(productId) {
    // Implementation for product comparison
    showToast('Comparison feature coming soon!', 'info');
}

function viewDetails(productId) {
    // Navigate to product details page
    window.location.href = `product_details.php?id=${productId}`;
}

function shareProduct(productId) {
    // Share functionality
    if(navigator.share) {
        navigator.share({
            title: 'Check out this investment opportunity!',
            url: window.location.href
        });
    } else {
        // Fallback copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        showToast('Link copied to clipboard!', 'success');
    }
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#28a745' : type === 'info' ? '#17a2b8' : '#ffc107'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideInRight 0.3s ease;
    `;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'info' ? 'info-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Initial render
filterAndRenderProducts();

// Buy product function (assuming this exists elsewhere)
function buyProduct(productId, price) {
    // This function should be defined in your main layout or included JS
    console.log('Buying product:', productId, 'Price:', price);
    // Implement your purchase logic here
}
</script>