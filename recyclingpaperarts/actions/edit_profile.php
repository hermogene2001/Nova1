<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enhanced security check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verify user role
if ($_SESSION['role'] !== 'client') {
    header("Location: unauthorized.php");
    exit;
}

include '../includes/db.php';

// Initialize variables
$phoneNumber = $firstName = $lastName = '';
$userId = $_SESSION['user_id'];
$successMessage = $errorMessage = '';

// Fetch the user's current information with error handling
try {
    $sql = "SELECT phone_number, fname, lname FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($phoneNumber, $firstName, $lastName);
    $stmt->fetch();
    $stmt->close();
} catch (Exception $e) {
    $errorMessage = "Error fetching user data: " . $e->getMessage();
}

// Handle form submission with validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $newPhoneNumber = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $newFirstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $newLastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($newPhoneNumber) || empty($newFirstName) || empty($newLastName)) {
        $errorMessage = "All fields are required!";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $newPhoneNumber)) {
        $errorMessage = "Please enter a valid phone number";
    } else {
        try {
            // Update the user's information
            $updateSql = "UPDATE users SET phone_number = ?, fname = ?, lname = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("sssi", $newPhoneNumber, $newFirstName, $newLastName, $userId);
            
            if ($updateStmt->execute()) {
                $successMessage = "Profile updated successfully!";
                // Update session data
                $_SESSION['phone_number'] = $newPhoneNumber;
                $_SESSION['fname'] = $newFirstName;
                $_SESSION['lname'] = $newLastName;
            } else {
                $errorMessage = "Error updating profile: " . $conn->error;
            }
            
            $updateStmt->close();
        } catch (Exception $e) {
            $errorMessage = "Database error: " . $e->getMessage();
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
    <title>Recycling Paper Arts - Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">
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
            --nav-bg: #0f172a;
        }

        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
            padding-bottom: 80px;
            position: relative;
            overflow-x: hidden;
        }

        .profile-container {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 16px;
            padding: 30px;
            margin: 30px auto;
            max-width: 600px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            color: var(--text);
            transition: all 0.3s ease;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
            padding: 20px 0;
            position: relative;
            z-index: 10;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .company-name {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            background: linear-gradient(135deg, var(--dark), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .profile-header {
            color: var(--primary);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
            position: relative;
            padding-bottom: 10px;
        }

        .profile-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .form-label {
            font-weight: 600;
            color: var(--text);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            padding: 10px 25px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), #047857);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
            padding: 10px 25px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .alert {
            border-radius: 10px;
        }

        /* Fixed bottom navbar */
        .fixed-bottom-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.18);
            padding: 10px 0;
            box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.08);
        }

        .fixed-bottom-nav .nav-link {
            color: var(--text-light);
            text-align: center;
            transition: all 0.3s ease;
        }

        .fixed-bottom-nav .nav-link i {
            font-size: 22px;
            display: block;
            margin: 0 auto 5px;
        }

        .fixed-bottom-nav .nav-link:hover,
        .fixed-bottom-nav .nav-link.active {
            color: var(--primary);
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 20px;
                margin: 20px auto;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="company-name">Recycling Paper Arts</div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="profile-container">
            <h3 class="profile-header"><i class="fas fa-user-edit"></i> Edit Profile</h3>
            
            <!-- Success/Error Messages -->
            <?php if ($successMessage): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="mb-4">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                           value="<?php echo htmlspecialchars($phoneNumber); ?>" 
                           pattern="[0-9]{10,15}" 
                           title="10-15 digit phone number" required>
                    <div class="form-text">Enter your phone number without spaces or special characters</div>
                </div>

                <div class="mb-4">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" 
                           value="<?php echo htmlspecialchars($firstName); ?>" 
                           pattern="[A-Za-z ]+" 
                           title="Letters only" required>
                </div>

                <div class="mb-4">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" 
                           value="<?php echo htmlspecialchars($lastName); ?>" 
                           pattern="[A-Za-z ]+" 
                           title="Letters only" required>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="../views/account.php" class="btn btn-secondary me-md-2">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Fixed Bottom Navigation -->
    <nav class="navbar fixed-bottom fixed-bottom-nav">
        <div class="container-fluid">
            <div class="row w-100">
                <div class="col-3">
                    <a href="../views/client_dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="../views/purchased.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Income</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="../views/invite.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Team</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="../views/account.php" class="nav-link active">
                        <i class="fas fa-user"></i>
                        <span>Account</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const phoneNumber = document.getElementById('phone_number').value;
            if (!/^[0-9]{10,15}$/.test(phoneNumber)) {
                alert('Please enter a valid phone number (10-15 digits)');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>