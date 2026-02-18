<?php
// Start session
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Include database connection
include('../includes/db.php');

// Enable error reporting for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Initialize variables
$success = $error = '';
$currentLinks = [];

// Fetch current social links
try {
    // First check if instagram column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM social_links LIKE 'instagram'");
    $hasInstagram = $checkColumn && $checkColumn->num_rows > 0;
        
    if ($hasInstagram) {
        $sql = "SELECT facebook, twitter, whatsapp, telegram, instagram FROM social_links LIMIT 1";
    } else {
        $sql = "SELECT facebook, twitter, whatsapp, telegram FROM social_links LIMIT 1";
    }
        
    $result = $conn->query($sql);
        
    if ($result && $result->num_rows > 0) {
        $currentLinks = $result->fetch_assoc();
        // Ensure instagram key exists even if column doesn't
        if (!isset($currentLinks['instagram'])) {
            $currentLinks['instagram'] = '';
        }
    } else {
        // Initialize empty array if no data found
        $currentLinks = [
            'facebook' => '',
            'twitter' => '',
            'whatsapp' => '',
            'telegram' => '',
            'instagram' => ''
        ];
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
    $currentLinks = [
        'facebook' => '',
        'twitter' => '',
        'whatsapp' => '',
        'telegram' => '',
        'instagram' => ''
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facebook = isset($_POST['facebook']) ? trim($_POST['facebook']) : '';
    $twitter = isset($_POST['twitter']) ? trim($_POST['twitter']) : '';
    $whatsapp = isset($_POST['whatsapp']) ? trim($_POST['whatsapp']) : '';
    $telegram = isset($_POST['telegram']) ? trim($_POST['telegram']) : '';
    $instagram = isset($_POST['instagram']) ? trim($_POST['instagram']) : '';
    
    // Validate URLs - allow empty values and basic URL format
    if ($facebook && !filter_var($facebook, FILTER_VALIDATE_URL) && !empty($facebook)) {
        $error = 'Invalid Facebook URL format';
    } elseif ($twitter && !filter_var($twitter, FILTER_VALIDATE_URL) && !empty($twitter)) {
        $error = 'Invalid Twitter URL format';
    } elseif ($whatsapp && !filter_var($whatsapp, FILTER_VALIDATE_URL) && !preg_match('/^[+\d\s\-\(\)]+$/', $whatsapp) && !empty($whatsapp)) {
        // WhatsApp can be phone number or URL
        $error = 'Invalid WhatsApp format (URL or phone number)';
    } elseif ($telegram && !filter_var($telegram, FILTER_VALIDATE_URL) && !empty($telegram)) {
        $error = 'Invalid Telegram URL format';
    } elseif ($instagram && !filter_var($instagram, FILTER_VALIDATE_URL) && !empty($instagram)) {
        $error = 'Invalid Instagram URL format';
    }
    
    if (empty($error)) {
        try {
            // Check if a record already exists
            $checkSql = "SELECT id FROM social_links LIMIT 1";
            $checkResult = $conn->query($checkSql);
            
            if ($checkResult && $checkResult->num_rows > 0) {
                // Update existing record
                if ($hasInstagram) {
                    $updateSql = "UPDATE social_links SET facebook=?, twitter=?, whatsapp=?, telegram=?, instagram=? WHERE id=(SELECT id FROM (SELECT id FROM social_links LIMIT 1) AS temp)";
                    $stmt = $conn->prepare($updateSql);
                    if ($stmt) {
                        $stmt->bind_param("sssss", $facebook, $twitter, $whatsapp, $telegram, $instagram);
                        if ($stmt->execute()) {
                            $success = 'Social media links updated successfully!';
                        } else {
                            $error = 'Failed to update social media links: ' . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $error = 'Failed to prepare update statement: ' . $conn->error;
                    }
                } else {
                    $updateSql = "UPDATE social_links SET facebook=?, twitter=?, whatsapp=?, telegram=? WHERE id=(SELECT id FROM (SELECT id FROM social_links LIMIT 1) AS temp)";
                    $stmt = $conn->prepare($updateSql);
                    if ($stmt) {
                        $stmt->bind_param("ssss", $facebook, $twitter, $whatsapp, $telegram);
                        if ($stmt->execute()) {
                            $success = 'Social media links updated successfully!';
                        } else {
                            $error = 'Failed to update social media links: ' . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $error = 'Failed to prepare update statement: ' . $conn->error;
                    }
                }
            } else {
                // Insert new record
                if ($hasInstagram) {
                    $insertSql = "INSERT INTO social_links (facebook, twitter, whatsapp, telegram, instagram) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insertSql);
                    if ($stmt) {
                        $stmt->bind_param("sssss", $facebook, $twitter, $whatsapp, $telegram, $instagram);
                        if ($stmt->execute()) {
                            $success = 'Social media links created successfully!';
                        } else {
                            $error = 'Failed to create social media links: ' . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $error = 'Failed to prepare insert statement: ' . $conn->error;
                    }
                } else {
                    $insertSql = "INSERT INTO social_links (facebook, twitter, whatsapp, telegram) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($insertSql);
                    if ($stmt) {
                        $stmt->bind_param("ssss", $facebook, $twitter, $whatsapp, $telegram);
                        if ($stmt->execute()) {
                            $success = 'Social media links created successfully!';
                        } else {
                            $error = 'Failed to create social media links: ' . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $error = 'Failed to prepare insert statement: ' . $conn->error;
                    }
                }
            }
            
            // Refresh the current links if successful
            if (empty($error) && !empty($success)) {
                if ($hasInstagram) {
                    $refreshSql = "SELECT facebook, twitter, whatsapp, telegram, instagram FROM social_links LIMIT 1";
                } else {
                    $refreshSql = "SELECT facebook, twitter, whatsapp, telegram FROM social_links LIMIT 1";
                }
                $refreshResult = $conn->query($refreshSql);
                if ($refreshResult && $refreshResult->num_rows > 0) {
                    $currentLinks = $refreshResult->fetch_assoc();
                    // Ensure instagram key exists
                    if (!isset($currentLinks['instagram'])) {
                        $currentLinks['instagram'] = '';
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Database operation failed: ' . $e->getMessage();
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
    <title>Recycling Paper Arts - Social Links Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="wave" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M0,50 Q25,25 50,50 T100,50 L100,100 L0,100 Z" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23wave)"/></svg>') center/cover;
            opacity: 0.3;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .ship-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        .form-container {
            padding: 40px;
        }

        .shipment-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #ff6b35;
        }

        .shipment-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e3e6ea;
        }

        .info-item strong {
            color: #2c3e50;
            display: block;
            margin-bottom: 5px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e3e6ea;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #ff6b35;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .platform-icons {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .platform-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .whatsapp { background: #25d366; }
        .telegram { background: #0088cc; }
        .facebook { background: #4267b2; }
        .twitter { background: #1da1f2; }

        .btn-container {
            text-align: center;
            margin-top: 30px;
        }

        .btn-update {
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }

        .btn-update:active {
            transform: translateY(0);
        }

        .tracking-section {
            background: #e8f4f8;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #bee5eb;
        }

        .tracking-section h3 {
            color: #0c5460;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #28a745;
            display: inline-block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/menu.php' ?>
    <div class="container">
        <div class="header">
            <div class="ship-icon">🚢</div>
            <h1>Recycling Paper Arts</h1>
            <p>Social Media Links Management Portal</p>
        </div>

        <div class="form-container">
            <!-- <div class="shipment-info">
                <h3>📦 Shipment Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Vessel Name:</strong>
                        Recycling Paper Arts
                    </div>
                    <div class="info-item">
                        <strong>Container ID:</strong>
                        COSU-7834562-1
                    </div>
                    <div class="info-item">
                        <strong>Port of Loading:</strong>
                        Shanghai, China
                    </div>
                    <div class="info-item">
                        <strong>Destination:</strong>
                        Global Network
                    </div>
                </div>
            </div> -->

            <div class="tracking-section">
                <h3>
                    <span class="status-indicator"></span>
                    Live Tracking Status
                </h3>
                <p>Your social media links are currently being processed and will be updated across all platforms simultaneously.</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    ✅ <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    ❌ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <div class="platform-icons">
                        <div class="platform-icon whatsapp">W</div>
                        <label for="whatsapp">WhatsApp Business Link</label>
                    </div>
                    <input type="url" 
                           id="whatsapp" 
                           name="whatsapp" 
                           value="<?php echo htmlspecialchars($currentLinks['whatsapp'] ?? ''); ?>" 
                           placeholder="https://wa.me/your-number">
                </div>

                <div class="form-group">
                    <div class="platform-icons">
                        <div class="platform-icon telegram">T</div>
                        <label for="telegram">Telegram Channel</label>
                    </div>
                    <input type="url" 
                           id="telegram" 
                           name="telegram" 
                           value="<?php echo htmlspecialchars($currentLinks['telegram'] ?? ''); ?>" 
                           placeholder="https://t.me/your-channel">
                </div>

                <div class="form-group">
                    <div class="platform-icons">
                        <div class="platform-icon facebook">F</div>
                        <label for="facebook">Facebook Page</label>
                    </div>
                    <input type="url" 
                           id="facebook" 
                           name="facebook" 
                           value="<?php echo htmlspecialchars($currentLinks['facebook'] ?? ''); ?>" 
                           placeholder="https://facebook.com/your-page">
                </div>

                <div class="form-group">
                    <div class="platform-icons">
                        <div class="platform-icon twitter">X</div>
                        <label for="twitter">X (Twitter) Profile</label>
                    </div>
                    <input type="url" 
                           id="twitter" 
                           name="twitter" 
                           value="<?php echo htmlspecialchars($currentLinks['twitter'] ?? ''); ?>" 
                           placeholder="https://x.com/your-profile">
                </div>

                <div class="form-group">
                    <div class="platform-icons">
                        <div class="platform-icon" style="background: linear-gradient(45deg, #405de6, #5851db, #833ab4, #c13584, #e1306c, #fd1d1d);">I</div>
                        <label for="instagram">Instagram Profile</label>
                    </div>
                    <input type="url" 
                           id="instagram" 
                           name="instagram" 
                           value="<?php echo htmlspecialchars($currentLinks['instagram'] ?? ''); ?>" 
                           placeholder="https://instagram.com/your-profile">
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn-update">
                        🚀 Update Social Links
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Add some interactive effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Add loading effect to submit button
        document.querySelector('.btn-update').addEventListener('click', function() {
            this.innerHTML = '⚡ Processing...';
            this.disabled = true;
            
            // Re-enable after form submission (in case of validation errors)
            setTimeout(() => {
                this.innerHTML = '🚀 Update Social Links';
                this.disabled = false;
            }, 3000);
        });
    </script>
</body>
</html>