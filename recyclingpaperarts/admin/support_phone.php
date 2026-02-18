<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

require_once('../includes/db.php');

// Initialize variables
$success_message = '';
$error_message = '';
$validation_errors = [];

// Fetch current support phone number
$support_phone_query = "SELECT support_phone FROM social_links WHERE id = 1";
$support_phone_result = mysqli_query($conn, $support_phone_query);
$current_support_phone = mysqli_fetch_assoc($support_phone_result)['support_phone'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $support_phone = trim($_POST['support_phone'] ?? '');
    
    // Validation
    if (!empty($support_phone)) {
        // Remove all non-digit characters except + for international format
        $clean_phone = preg_replace('/[^0-9+]/', '', $support_phone);
        
        // Validate phone format (10-15 digits, optionally starting with +)
        if (!preg_match('/^(\+?[0-9]{10,15})$/', $clean_phone)) {
            $validation_errors['support_phone'] = 'Please enter a valid phone number (10-15 digits, optionally with + prefix)';
        }
    }
    
    // Update if no validation errors
    if (empty($validation_errors)) {
        // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both insert and update
        $update_query = "INSERT INTO social_links (id, support_phone) VALUES (1, ?) ON DUPLICATE KEY UPDATE support_phone = VALUES(support_phone)";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("s", $support_phone);
        
        if ($stmt->execute()) {
            $success_message = "Support phone number updated successfully.";
            $current_support_phone = $support_phone;
        } else {
            $error_message = "Error updating support phone number: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Phone Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .main-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            margin: -15px -15px 0 -15px;
        }
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
        .alert {
            border-radius: 8px;
        }
        .feature-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-newspaper me-2"></i>Admin Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php"><i class="fas fa-users me-1"></i>Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="phone_management.php"><i class="fas fa-phone me-1"></i>Phone Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="support_phone.php"><i class="fas fa-headset me-1"></i>Support Phone</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php"><i class="fas fa-box me-1"></i>Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="update_social_links.php"><i class="fas fa-hashtag me-1"></i>SocialMedia</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-light" href="../actions/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="main-container">
            <div class="header-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-0">
                            <i class="fas fa-headset me-2"></i>
                            Support Phone Management
                        </h2>
                        <p class="mb-0 mt-1 opacity-75">Configure the phone number clients can call for assistance</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="d-flex gap-2 justify-content-md-end">
                            <a href="dashboard.php" class="btn btn-light">
                                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-phone-alt me-2"></i>
                                    Configure Support Phone Number
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-4">
                                        <label for="support_phone" class="form-label">
                                            <i class="fas fa-phone me-2"></i>Support Phone Number
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-phone"></i>
                                            </span>
                                            <input type="tel" 
                                                   class="form-control <?php echo isset($validation_errors['support_phone']) ? 'is-invalid' : ''; ?>" 
                                                   id="support_phone" 
                                                   name="support_phone" 
                                                   value="<?php echo htmlspecialchars($current_support_phone); ?>"
                                                   placeholder="+250 XXX XXX XXX"
                                                   maxlength="20">
                                        </div>
                                        <?php if (isset($validation_errors['support_phone'])): ?>
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                <?php echo $validation_errors['support_phone']; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Enter the phone number clients can call for support. Leave empty to disable.
                                            Format: +250 XXX XXX XXX or 07XXXXXXXX
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Save Support Phone
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary">
                                            <i class="fas fa-redo me-2"></i>Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    Current Configuration
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-phone me-2 text-primary"></i>Current Support Phone:</h6>
                                        <p class="fs-4 fw-bold">
                                            <?php if (!empty($current_support_phone)): ?>
                                                <?php echo htmlspecialchars($current_support_phone); ?>
                                                <span class="badge bg-success ms-2">Active</span>
                                            <?php else: ?>
                                                <span class="text-muted">Not configured</span>
                                                <span class="badge bg-secondary ms-2">Disabled</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-eye me-2 text-info"></i>Visibility:</h6>
                                        <p>
                                            <?php if (!empty($current_support_phone)): ?>
                                                <span class="badge bg-success">Visible to clients</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Hidden from clients</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="feature-card p-4 mb-4">
                            <div class="text-center">
                                <div class="feature-icon text-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5>Clients Will See This</h5>
                                <p class="text-muted">The support phone number will appear in client dashboards and help sections when clients encounter system problems.</p>
                            </div>
                        </div>

                        <div class="feature-card p-4 mb-4">
                            <div class="text-center">
                                <div class="feature-icon text-success">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <h5>Direct Support</h5>
                                <p class="text-muted">Clients can call directly for immediate assistance with account issues, transactions, or technical problems.</p>
                            </div>
                        </div>

                        <div class="feature-card p-4">
                            <div class="text-center">
                                <div class="feature-icon text-info">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <h5>Availability Notice</h5>
                                <p class="text-muted">Consider adding operating hours and response time expectations for better client service.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-format phone number input
        document.getElementById('support_phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            
            // Format based on length
            if (value.length > 0) {
                if (value.startsWith('250')) {
                    // Rwandan format: +250 XXX XXX XXX
                    if (value.length >= 12) {
                        value = '+250 ' + value.substring(3, 6) + ' ' + value.substring(6, 9) + ' ' + value.substring(9, 12);
                    } else if (value.length >= 9) {
                        value = '+250 ' + value.substring(3, 6) + ' ' + value.substring(6, 9) + ' ' + value.substring(9);
                    } else if (value.length >= 6) {
                        value = '+250 ' + value.substring(3, 6) + ' ' + value.substring(6);
                    } else if (value.length > 3) {
                        value = '+250 ' + value.substring(3);
                    } else {
                        value = '+250 ';
                    }
                } else if (value.startsWith('0') && value.length > 1) {
                    // Local format: 07XX XXX XXX
                    if (value.length >= 10) {
                        value = '0' + value.substring(1, 4) + ' ' + value.substring(4, 7) + ' ' + value.substring(7, 10);
                    } else if (value.length >= 7) {
                        value = '0' + value.substring(1, 4) + ' ' + value.substring(4, 7) + ' ' + value.substring(7);
                    } else if (value.length >= 4) {
                        value = '0' + value.substring(1, 4) + ' ' + value.substring(4);
                    } else {
                        value = '0' + value.substring(1);
                    }
                } else {
                    // Generic format
                    if (value.length > 3) {
                        value = value.substring(0, 3) + ' ' + value.substring(3);
                    }
                }
            }
            
            e.target.value = value;
        });

        // Show current phone on page load
        document.addEventListener('DOMContentLoaded', function() {
            const currentPhone = document.querySelector('.card-body .fs-4').textContent.trim();
            if (currentPhone && currentPhone !== 'Not configured') {
                console.log('Current support phone:', currentPhone);
            }
        });
    </script>
</body>
</html>