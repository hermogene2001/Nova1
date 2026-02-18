<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $min_investment = $_POST['min_investment'];
    $profit_rate = $_POST['profit_rate'];
    $cycle = $_POST['cycle'];
    $cycle_unit = $_POST['cycle_unit'];
    $payout_type = $_POST['payout_type'];
    $penalty_rate = $_POST['penalty_rate'];
    $risk_level = $_POST['risk_level'];
    // $description = $_POST['description'] ?? '';
    // $features = $_POST['features'] ?? '';
    
    // Handle file upload with validation
    $image = 'default.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/products/';
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowedTypes) && $_FILES['image']['size'] <= 5000000) { // 5MB limit
            $filename = 'product_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $image = $filename;
            }
        }
    }

    // Calculate maturity_date and daily earning
    $start_date = date('Y-m-d H:i:s');
    $maturity_date = date('Y-m-d H:i:s', strtotime("+$cycle $cycle_unit"));
    $daily_earning = ($profit_rate / 100) * $price / 365; // Simple daily calculation

    $stmt = $pdo->prepare("
        INSERT INTO products_compound (
            name, price, min_investment, profit_rate, cycle, cycle_unit,
            daily_earning, payout_type, penalty_rate, risk_level, image,
            start_date, maturity_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $name, $price, $min_investment, $profit_rate, $cycle, $cycle_unit,
        $daily_earning, $payout_type, $penalty_rate, $risk_level, $image,
        $start_date, $maturity_date
    ]);

    header("Location: manage-products.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Investment Product | Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .form-container {
            padding: 40px 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 1em;
        }

        .form-group label i {
            margin-right: 8px;
            color: #4facfe;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #4facfe;
            background: white;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        .input-group input {
            flex: 1;
        }

        .input-group select {
            flex: 0 0 120px;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: block;
            padding: 15px;
            border: 2px dashed #4facfe;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .file-upload-label:hover {
            background: #e3f2fd;
            border-color: #2196f3;
        }

        .file-upload-label i {
            font-size: 2em;
            color: #4facfe;
            margin-bottom: 10px;
        }

        .risk-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 10px;
        }

        .risk-option {
            position: relative;
        }

        .risk-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .risk-option label {
            display: block;
            padding: 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .risk-option input[type="radio"]:checked + label {
            border-color: #4facfe;
            background: #e3f2fd;
            color: #1976d2;
        }

        .risk-low label { border-left: 4px solid #4caf50; }
        .risk-medium label { border-left: 4px solid #ff9800; }
        .risk-high label { border-left: 4px solid #f44336; }

        .payout-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .payout-option {
            position: relative;
        }

        .payout-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payout-option label {
            display: block;
            padding: 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .payout-option input[type="radio"]:checked + label {
            border-color: #4facfe;
            background: #e3f2fd;
            color: #1976d2;
        }

        .calculator-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .calculator-section h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .calculation-result {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .calc-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #4facfe;
        }

        .calc-item .value {
            font-size: 1.5em;
            font-weight: bold;
            color: #1976d2;
        }

        .calc-item .label {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 50px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 30px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 172, 254, 0.3);
        }

        .preview-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .preview-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e1e1e1;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .risk-selector {
                grid-template-columns: 1fr;
            }
            
            .payout-selector {
                grid-template-columns: 1fr;
            }
        }

        .tooltip {
            position: relative;
            display: inline-block;
            cursor: help;
        }

        .tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8em;
            white-space: nowrap;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Investment Product Creator</h1>
            <p>Design attractive investment opportunities for your clients</p>
        </div>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <div class="form-grid">
                    <!-- Product Image -->
                    <div class="form-group full-width">
                        <label><i class="fas fa-image"></i> Product Image</label>
                        <div class="file-upload">
                            <input type="file" name="image" accept="image/*" id="imageInput">
                            <label for="imageInput" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div>Click to upload product image</div>
                                <small>Supported: JPG, PNG, GIF, WebP (Max 5MB)</small>
                            </label>
                        </div>
                    </div>

                    <!-- Product Name -->
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Product Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Premium Growth Plan">
                    </div>

                    <!-- Price -->
                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> Product Price ($)</label>
                        <input type="number" name="price" class="form-control" step="0.01" required placeholder="1000.00" id="priceInput">
                    </div>

                    <!-- Min Investment -->
                    <div class="form-group">
                        <label><i class="fas fa-coins"></i> Minimum Investment ($)</label>
                        <input type="number" name="min_investment" class="form-control" step="0.01" required placeholder="100.00">
                    </div>

                    <!-- Profit Rate -->
                    <div class="form-group">
                        <label><i class="fas fa-percentage"></i> Annual Profit Rate (%) 
                            <span class="tooltip" data-tooltip="Expected annual return percentage">ⓘ</span>
                        </label>
                        <input type="number" name="profit_rate" class="form-control" step="0.01" required placeholder="12.50" id="profitInput">
                    </div>

                    <!-- Investment Cycle -->
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Investment Cycle</label>
                        <div class="input-group">
                            <input type="number" name="cycle" class="form-control" required placeholder="1" id="cycleInput">
                            <select name="cycle_unit" class="form-control">
                                <option value="days">Days</option>
                                <option value="months">Months</option>
                                <option value="years" selected>Years</option>
                            </select>
                        </div>
                    </div>

                    <!-- Payout Type -->
                    <div class="form-group full-width">
                        <label><i class="fas fa-money-bill-wave"></i> Payout Schedule</label>
                        <div class="payout-selector">
                            <div class="payout-option">
                                <input type="radio" name="payout_type" value="end_of_cycle" id="payout_end" checked>
                                <label for="payout_end">
                                    <i class="fas fa-calendar-check"></i><br>
                                    End of Cycle
                                </label>
                            </div>
                            <div class="payout-option">
                                <input type="radio" name="payout_type" value="monthly" id="payout_monthly">
                                <label for="payout_monthly">
                                    <i class="fas fa-calendar"></i><br>
                                    Monthly
                                </label>
                            </div>
                            <div class="payout-option">
                                <input type="radio" name="payout_type" value="quarterly" id="payout_quarterly">
                                <label for="payout_quarterly">
                                    <i class="fas fa-calendar-alt"></i><br>
                                    Quarterly
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Risk Level -->
                    <div class="form-group full-width">
                        <label><i class="fas fa-shield-alt"></i> Risk Level</label>
                        <div class="risk-selector">
                            <div class="risk-option risk-low">
                                <input type="radio" name="risk_level" value="low" id="risk_low">
                                <label for="risk_low">
                                    <i class="fas fa-shield-alt" style="color: #4caf50;"></i><br>
                                    Low Risk
                                </label>
                            </div>
                            <div class="risk-option risk-medium">
                                <input type="radio" name="risk_level" value="medium" id="risk_medium" checked>
                                <label for="risk_medium">
                                    <i class="fas fa-balance-scale" style="color: #ff9800;"></i><br>
                                    Medium Risk
                                </label>
                            </div>
                            <div class="risk-option risk-high">
                                <input type="radio" name="risk_level" value="high" id="risk_high">
                                <label for="risk_high">
                                    <i class="fas fa-exclamation-triangle" style="color: #f44336;"></i><br>
                                    High Risk
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Penalty Rate -->
                    <div class="form-group">
                        <label><i class="fas fa-ban"></i> Early Withdrawal Penalty (%)</label>
                        <input type="number" name="penalty_rate" class="form-control" step="0.01" value="5.00" placeholder="5.00">
                    </div>

                    <!-- Description -->
                    <!-- <div class="form-group full-width">
                        <label><i class="fas fa-align-left"></i> Product Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the investment product, its benefits, and target audience..."></textarea>
                    </div> -->

                    <!-- Key Features -->
                    <!-- <div class="form-group full-width">
                        <label><i class="fas fa-star"></i> Key Features</label>
                        <textarea name="features" class="form-control" rows="3" placeholder="List key features separated by commas (e.g., Guaranteed returns, Professional management, Flexible withdrawal...)"></textarea>
                    </div>-->
                </div> 

                <!-- Investment Calculator -->
                <div class="calculator-section">
                    <h3><i class="fas fa-calculator"></i> Investment Calculator Preview</h3>
                    <p>See how your product will perform with these settings:</p>
                    <div class="calculation-result" id="calculationResult">
                        <div class="calc-item">
                            <div class="value" id="dailyEarning">$0.00</div>
                            <div class="label">Daily Earning</div>
                        </div>
                        <div class="calc-item">
                            <div class="value" id="monthlyEarning">$0.00</div>
                            <div class="label">Monthly Earning</div>
                        </div>
                        <div class="calc-item">
                            <div class="value" id="totalReturn">$0.00</div>
                            <div class="label">Total Return</div>
                        </div>
                        <div class="calc-item">
                            <div class="value" id="maturityDate">-</div>
                            <div class="label">Maturity Date</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-plus-circle"></i> Create Investment Product
                </button>
            </form>
        </div>
    </div>

    <script>
        // Real-time calculation updates
        function updateCalculations() {
            const price = parseFloat(document.getElementById('priceInput').value) || 0;
            const profitRate = parseFloat(document.getElementById('profitInput').value) || 0;
            const cycle = parseInt(document.getElementById('cycleInput').value) || 1;
            const cycleUnit = document.querySelector('select[name="cycle_unit"]').value;
            
            if (price > 0 && profitRate > 0) {
                const annualReturn = (profitRate / 100) * price;
                const dailyEarning = annualReturn / 365;
                const monthlyEarning = annualReturn / 12;
                
                let totalDays = cycle;
                if (cycleUnit === 'months') totalDays = cycle * 30;
                else if (cycleUnit === 'years') totalDays = cycle * 365;
                
                const totalReturn = price + (dailyEarning * totalDays);
                
                // Update display
                document.getElementById('dailyEarning').textContent = '$' + dailyEarning.toFixed(2);
                document.getElementById('monthlyEarning').textContent = '$' + monthlyEarning.toFixed(2);
                document.getElementById('totalReturn').textContent = '$' + totalReturn.toFixed(2);
                
                // Calculate maturity date
                const today = new Date();
                const maturityDate = new Date(today);
                if (cycleUnit === 'days') {
                    maturityDate.setDate(today.getDate() + cycle);
                } else if (cycleUnit === 'months') {
                    maturityDate.setMonth(today.getMonth() + cycle);
                } else if (cycleUnit === 'years') {
                    maturityDate.setFullYear(today.getFullYear() + cycle);
                }
                
                document.getElementById('maturityDate').textContent = maturityDate.toLocaleDateString();
            }
        }

        // Add event listeners for real-time updates
        document.getElementById('priceInput').addEventListener('input', updateCalculations);
        document.getElementById('profitInput').addEventListener('input', updateCalculations);
        document.getElementById('cycleInput').addEventListener('input', updateCalculations);
        document.querySelector('select[name="cycle_unit"]').addEventListener('change', updateCalculations);

        // File upload preview
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const label = document.querySelector('.file-upload-label');
                label.innerHTML = `
                    <i class="fas fa-check-circle" style="color: #4caf50;"></i>
                    <div>${file.name}</div>
                    <small>File selected successfully</small>
                `;
            }
        });

        // Form validation
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const price = parseFloat(document.getElementById('priceInput').value);
            const minInvestment = parseFloat(document.querySelector('input[name="min_investment"]').value);
            
            if (minInvestment > price) {
                e.preventDefault();
                alert('Minimum investment cannot be greater than the product price!');
                return false;
            }
            
            return true;
        });

        // Initialize calculations on page load
        updateCalculations();
    </script>
</body>
</html>