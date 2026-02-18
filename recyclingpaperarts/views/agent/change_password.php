<?php
session_start();
include '../../includes/db.php';

// Ensure the user is logged in as an agent
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent' || !isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Initialize variables
$message = '';
$message_type = 'info';
$validation_errors = [];
$password_strength = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($current_password)) {
        $validation_errors['current'] = 'Current password is required.';
    }
    
    if (empty($new_password)) {
        $validation_errors['new'] = 'New password is required.';
    } elseif (strlen($new_password) < 8) {
        $validation_errors['new'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $new_password)) {
        $validation_errors['new'] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
    }
    
    if (empty($confirm_password)) {
        $validation_errors['confirm'] = 'Please confirm your new password.';
    } elseif ($new_password !== $confirm_password) {
        $validation_errors['confirm'] = 'Passwords do not match.';
    }
    
    // Check if new password is different from current
    if (empty($validation_errors) && $current_password === $new_password) {
        $validation_errors['new'] = 'New password must be different from current password.';
    }
    
    // Process if no validation errors
    if (empty($validation_errors)) {
        $user_id = $_SESSION['user_id'];

        // Fetch the current password from the database
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        // Verify current password
        if (!password_verify($current_password, $hashed_password)) {
            $validation_errors['current'] = 'Current password is incorrect.';
        } else {
            // Hash the new password
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('si', $new_hashed_password, $user_id);

            if ($update_stmt->execute()) {
                $message = 'Password updated successfully! Please log in again for security.';
                $message_type = 'success';
                // Clear session to force re-login
                session_destroy();
                header('Refresh: 3; url=../login.php');
            } else {
                $message = 'An error occurred while updating your password. Please try again.';
                $message_type = 'error';
            }
            $update_stmt->close();
        }
    }
}

// Calculate password strength
function getPasswordStrength($password) {
    $score = 0;
    
    if (strlen($password) >= 8) $score += 20;
    if (strlen($password) >= 12) $score += 10;
    if (preg_match('/[a-z]/', $password)) $score += 15;
    if (preg_match('/[A-Z]/', $password)) $score += 15;
    if (preg_match('/\d/', $password)) $score += 20;
    if (preg_match('/[^a-zA-Z\d]/', $password)) $score += 20;
    
    return min($score, 100);
}

// Build the page content
ob_start(); ?>

<?php if ($message): ?>
<div class="agent-alert <?php echo 'agent-alert-' . $message_type; ?>">
    <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : ($message_type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'); ?>"></i>
    <?php echo htmlspecialchars($message); ?>
    <?php if ($message_type === 'success'): ?>
        <div class="mt-2 small">Redirecting to login page...</div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="agent-card">
            <div class="agent-card-header">
                <i class="fas fa-key"></i>
                Change Password
            </div>
            <div class="agent-card-body">
                <div class="alert alert-info mb-4">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>Security Notice:</strong> For your protection, you'll need to log in again after changing your password.
                </div>
                
                <form method="POST" action="" id="passwordForm">
                    <div class="mb-4">
                        <label for="current_password" class="agent-form-label">
                            <i class="fas fa-lock me-2"></i>Current Password
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="agent-form-control <?php echo isset($validation_errors['current']) ? 'is-invalid' : ''; ?>" 
                                   id="current_password" 
                                   name="current_password" 
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleCurrent">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($validation_errors['current'])): ?>
                            <div class="invalid-feedback d-block mt-2">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                <?php echo $validation_errors['current']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="new_password" class="agent-form-label">
                            <i class="fas fa-key me-2"></i>New Password
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="agent-form-control <?php echo isset($validation_errors['new']) ? 'is-invalid' : ''; ?>" 
                                   id="new_password" 
                                   name="new_password" 
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleNew">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        
                        <!-- Password Strength Meter -->
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Password Strength</small>
                                <small id="strengthText" class="fw-medium">Weak</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div id="strengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div class="mt-3">
                            <small class="text-muted d-block mb-2">Password must contain:</small>
                            <ul class="list-unstyled small mb-0">
                                <li id="req-length" class="text-danger">
                                    <i class="fas fa-times-circle me-2"></i>At least 8 characters
                                </li>
                                <li id="req-upper" class="text-danger">
                                    <i class="fas fa-times-circle me-2"></i>One uppercase letter
                                </li>
                                <li id="req-lower" class="text-danger">
                                    <i class="fas fa-times-circle me-2"></i>One lowercase letter
                                </li>
                                <li id="req-number" class="text-danger">
                                    <i class="fas fa-times-circle me-2"></i>One number
                                </li>
                                <li id="req-special" class="text-danger">
                                    <i class="fas fa-times-circle me-2"></i>One special character
                                </li>
                            </ul>
                        </div>
                        
                        <?php if (isset($validation_errors['new'])): ?>
                            <div class="invalid-feedback d-block mt-2">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                <?php echo $validation_errors['new']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="agent-form-label">
                            <i class="fas fa-check-circle me-2"></i>Confirm New Password
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="agent-form-control <?php echo isset($validation_errors['confirm']) ? 'is-invalid' : ''; ?>" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($validation_errors['confirm'])): ?>
                            <div class="invalid-feedback d-block mt-2">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                <?php echo $validation_errors['confirm']; ?>
                            </div>
                        <?php endif; ?>
                        <div id="passwordMatch" class="mt-2"></div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn-agent btn-agent-primary btn-lg" id="submitBtn">
                            <i class="fas fa-save me-2"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="agent-card sticky-top" style="top: 20px;">
            <div class="agent-card-header">
                <i class="fas fa-shield-alt"></i>
                Security Tips
            </div>
            <div class="agent-card-body">
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-1">Use Strong Passwords</h6>
                                <p class="mb-0 text-muted small">Include uppercase, lowercase, numbers, and symbols</p>
                            </div>
                        </div>
                    </li>
                    <li class="mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-times-circle text-danger"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-1">Avoid Common Words</h6>
                                <p class="mb-0 text-muted small">Don't use dictionary words or personal information</p>
                            </div>
                        </div>
                    </li>
                    <li class="mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-sync text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-1">Regular Updates</h6>
                                <p class="mb-0 text-muted small">Change passwords periodically for better security</p>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-mobile-alt text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-1">Two-Factor Authentication</h6>
                                <p class="mb-0 text-muted small">Enable 2FA when available for extra protection</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-4">
    <a href="../agent_dashboard.php" class="btn-agent btn-agent-outline">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<script>
// Password visibility toggles
document.getElementById('toggleCurrent').addEventListener('click', function() {
    togglePassword('current_password', this);
});

document.getElementById('toggleNew').addEventListener('click', function() {
    togglePassword('new_password', this);
});

document.getElementById('toggleConfirm').addEventListener('click', function() {
    togglePassword('confirm_password', this);
});

function togglePassword(fieldId, button) {
    const field = document.getElementById(fieldId);
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strength = getPasswordStrength(password);
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    // Update strength bar
    strengthBar.style.width = strength + '%';
    
    // Update strength text and color
    if (strength < 40) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'Weak';
        strengthText.className = 'fw-medium text-danger';
    } else if (strength < 70) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'Medium';
        strengthText.className = 'fw-medium text-warning';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'Strong';
        strengthText.className = 'fw-medium text-success';
    }
    
    // Update requirements
    updateRequirements(password);
});

// Password match checker
const confirmPasswordField = document.getElementById('confirm_password');
const newPasswordField = document.getElementById('new_password');
const matchIndicator = document.getElementById('passwordMatch');

confirmPasswordField.addEventListener('input', checkPasswordMatch);
newPasswordField.addEventListener('input', checkPasswordMatch);

function checkPasswordMatch() {
    const newPassword = newPasswordField.value;
    const confirmPassword = confirmPasswordField.value;
    
    if (confirmPassword === '') {
        matchIndicator.innerHTML = '';
        return;
    }
    
    if (newPassword === confirmPassword) {
        matchIndicator.innerHTML = '<small class="text-success"><i class="fas fa-check-circle me-1"></i>Passwords match</small>';
    } else {
        matchIndicator.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Passwords do not match</small>';
    }
}

function updateRequirements(password) {
    // Length requirement
    const lengthReq = document.getElementById('req-length');
    if (password.length >= 8) {
        lengthReq.className = 'text-success';
        lengthReq.innerHTML = '<i class="fas fa-check-circle me-2"></i>At least 8 characters';
    } else {
        lengthReq.className = 'text-danger';
        lengthReq.innerHTML = '<i class="fas fa-times-circle me-2"></i>At least 8 characters';
    }
    
    // Uppercase requirement
    const upperReq = document.getElementById('req-upper');
    if (/[A-Z]/.test(password)) {
        upperReq.className = 'text-success';
        upperReq.innerHTML = '<i class="fas fa-check-circle me-2"></i>One uppercase letter';
    } else {
        upperReq.className = 'text-danger';
        upperReq.innerHTML = '<i class="fas fa-times-circle me-2"></i>One uppercase letter';
    }
    
    // Lowercase requirement
    const lowerReq = document.getElementById('req-lower');
    if (/[a-z]/.test(password)) {
        lowerReq.className = 'text-success';
        lowerReq.innerHTML = '<i class="fas fa-check-circle me-2"></i>One lowercase letter';
    } else {
        lowerReq.className = 'text-danger';
        lowerReq.innerHTML = '<i class="fas fa-times-circle me-2"></i>One lowercase letter';
    }
    
    // Number requirement
    const numberReq = document.getElementById('req-number');
    if (/\d/.test(password)) {
        numberReq.className = 'text-success';
        numberReq.innerHTML = '<i class="fas fa-check-circle me-2"></i>One number';
    } else {
        numberReq.className = 'text-danger';
        numberReq.innerHTML = '<i class="fas fa-times-circle me-2"></i>One number';
    }
    
    // Special character requirement
    const specialReq = document.getElementById('req-special');
    if (/[^a-zA-Z\d]/.test(password)) {
        specialReq.className = 'text-success';
        specialReq.innerHTML = '<i class="fas fa-check-circle me-2"></i>One special character';
    } else {
        specialReq.className = 'text-danger';
        specialReq.innerHTML = '<i class="fas fa-times-circle me-2"></i>One special character';
    }
}

function getPasswordStrength(password) {
    let score = 0;
    
    if (password.length >= 8) score += 20;
    if (password.length >= 12) score += 10;
    if (/[a-z]/.test(password)) score += 15;
    if (/[A-Z]/.test(password)) score += 15;
    if (/\d/.test(password)) score += 20;
    if (/[^a-zA-Z\d]/.test(password)) score += 20;
    
    return Math.min(score, 100);
}

// Form submission handler
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<span class="agent-loading"></span> Changing Password...';
    submitBtn.disabled = true;
});
</script>

<?php
$page_content = ob_get_clean();
$agent_page_title = 'Change Password';

// Include the layout template
include '../agent/_layout.php';
?>