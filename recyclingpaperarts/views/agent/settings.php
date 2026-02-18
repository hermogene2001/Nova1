<?php
session_start();

// Ensure the user is logged in as an agent and the ID is set
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent' || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Database connection
require_once('../../includes/db.php');

// Initialize variables
$agent_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';
$validation_errors = [];

// Fetch the agent's current details using prepared statement
$stmt = $conn->prepare("SELECT fname, lname, phone_number, referral_code, email FROM users WHERE id = ?");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$agent = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch client details if client_id is provided
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;
$client = null;

if ($client_id) {
    $stmt = $conn->prepare("SELECT id, balance, phone_number, fname, lname FROM users WHERE id = ? AND role = 'client'");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $client = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Handle form submission for updating agent settings and client balance
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update agent's personal details
    if (isset($_POST['phone_number']) && isset($_POST['fname']) && isset($_POST['lname'])) {
        $new_phone_number = trim($_POST['phone_number']);
        $new_fname = trim($_POST['fname']);
        $new_lname = trim($_POST['lname']);
        
        // Validation
        if (empty($new_phone_number)) {
            $validation_errors['phone'] = 'Phone number is required.';
        } elseif (!preg_match('/^[0-9+\s\-()]{10,20}$/', $new_phone_number)) {
            $validation_errors['phone'] = 'Please enter a valid phone number.';
        }
        
        if (empty($new_fname)) {
            $validation_errors['fname'] = 'First name is required.';
        } elseif (strlen($new_fname) < 2) {
            $validation_errors['fname'] = 'First name must be at least 2 characters.';
        }
        
        if (empty($new_lname)) {
            $validation_errors['lname'] = 'Last name is required.';
        } elseif (strlen($new_lname) < 2) {
            $validation_errors['lname'] = 'Last name must be at least 2 characters.';
        }
        
        // Update if no validation errors
        if (empty($validation_errors)) {
            $stmt = $conn->prepare("UPDATE users SET phone_number = ?, fname = ?, lname = ? WHERE id = ?");
            $stmt->bind_param("sssi", $new_phone_number, $new_fname, $new_lname, $agent_id);
            
            if ($stmt->execute()) {
                $success_message = "Settings updated successfully.";
                // Refresh agent data
                $stmt->close();
                $stmt = $conn->prepare("SELECT fname, lname, phone_number, referral_code, email FROM users WHERE id = ?");
                $stmt->bind_param("i", $agent_id);
                $stmt->execute();
                $agent = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            } else {
                $error_message = "Error updating settings: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Update client balance
    if (isset($_POST['client_balance']) && $client_id) {
        $new_balance = floatval($_POST['client_balance']);
        
        // Validation
        if ($new_balance < 0) {
            $validation_errors['balance'] = 'Balance cannot be negative.';
        }
        
        if (empty($validation_errors)) {
            $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ? AND role = 'client'");
            $stmt->bind_param("di", $new_balance, $client_id);
            
            if ($stmt->execute()) {
                $success_message = "Client balance updated successfully.";
                // Refresh client data
                $stmt->close();
                $stmt = $conn->prepare("SELECT id, balance, phone_number, fname, lname FROM users WHERE id = ? AND role = 'client'");
                $stmt->bind_param("i", $client_id);
                $stmt->execute();
                $client = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            } else {
                $error_message = "Error updating client balance: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Build the page content
ob_start(); ?>

<?php if ($success_message): ?>
<div class="agent-alert agent-alert-success">
    <i class="fas fa-check-circle"></i>
    <?php echo htmlspecialchars($success_message); ?>
</div>
<?php endif; ?>

<?php if ($error_message): ?>
<div class="agent-alert agent-alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <?php echo htmlspecialchars($error_message); ?>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="agent-card">
            <div class="agent-card-header">
                <i class="fas fa-user-cog"></i>
                Personal Information
            </div>
            <div class="agent-card-body">
                <form method="POST" action="" class="row">
                    <div class="col-md-6 mb-4">
                        <div class="agent-form-group">
                            <label for="fname" class="agent-form-label">
                                <i class="fas fa-user me-2"></i>First Name
                            </label>
                            <input type="text" 
                                   class="agent-form-control <?php echo isset($validation_errors['fname']) ? 'is-invalid' : ''; ?>" 
                                   id="fname" 
                                   name="fname" 
                                   value="<?php echo htmlspecialchars($agent['fname'] ?? ''); ?>" 
                                   required>
                            <?php if (isset($validation_errors['fname'])): ?>
                                <div class="invalid-feedback d-block mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    <?php echo $validation_errors['fname']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="agent-form-group">
                            <label for="lname" class="agent-form-label">
                                <i class="fas fa-user me-2"></i>Last Name
                            </label>
                            <input type="text" 
                                   class="agent-form-control <?php echo isset($validation_errors['lname']) ? 'is-invalid' : ''; ?>" 
                                   id="lname" 
                                   name="lname" 
                                   value="<?php echo htmlspecialchars($agent['lname'] ?? ''); ?>" 
                                   required>
                            <?php if (isset($validation_errors['lname'])): ?>
                                <div class="invalid-feedback d-block mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    <?php echo $validation_errors['lname']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="agent-form-group">
                            <label for="phone_number" class="agent-form-label">
                                <i class="fas fa-phone me-2"></i>Phone Number
                            </label>
                            <input type="tel" 
                                   class="agent-form-control <?php echo isset($validation_errors['phone']) ? 'is-invalid' : ''; ?>" 
                                   id="phone_number" 
                                   name="phone_number" 
                                   value="<?php echo htmlspecialchars($agent['phone_number'] ?? ''); ?>" 
                                   placeholder="+250 XXX XXX XXX"
                                   required>
                            <?php if (isset($validation_errors['phone'])): ?>
                                <div class="invalid-feedback d-block mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    <?php echo $validation_errors['phone']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="agent-form-group">
                            <label class="agent-form-label">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                            <input type="email" 
                                   class="agent-form-control" 
                                   value="<?php echo htmlspecialchars($agent['email'] ?? 'Not provided'); ?>" 
                                   disabled>
                            <div class="form-text text-muted mt-2">
                                <i class="fas fa-info-circle me-1"></i>Contact administrator to change email
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 mb-4">
                        <div class="agent-form-group">
                            <label class="agent-form-label">
                                <i class="fas fa-user-tag me-2"></i>Referral Code
                            </label>
                            <div class="input-group">
                                <input type="text" 
                                       class="agent-form-control" 
                                       value="<?php echo htmlspecialchars($agent['referral_code'] ?? ''); ?>" 
                                       readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('<?php echo $agent['referral_code'] ?? ''; ?>')">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                            <div class="form-text text-muted mt-2">
                                <i class="fas fa-share-alt me-1"></i>Share this code with others to earn referral commissions
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn-agent btn-agent-primary">
                            <i class="fas fa-save me-2"></i> Update Personal Information
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($client_id && $client): ?>
        <div class="agent-card mt-4">
            <div class="agent-card-header">
                <i class="fas fa-wallet"></i>
                Update Client Balance
            </div>
            <div class="agent-card-body">
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Client:</strong> <?php echo htmlspecialchars(($client['fname'] ?? '') . ' ' . ($client['lname'] ?? '')); ?>
                    (ID: <?php echo $client['id']; ?>)
                </div>
                
                <form method="POST" action="">
                    <div class="agent-form-group">
                        <label for="client_phone" class="agent-form-label">
                            <i class="fas fa-phone me-2"></i>Client Phone Number
                        </label>
                        <input type="text" 
                               class="agent-form-control" 
                               id="client_phone" 
                               value="<?php echo htmlspecialchars($client['phone_number'] ?? ''); ?>" 
                               disabled>
                    </div>
                    
                    <div class="agent-form-group">
                        <label for="client_balance" class="agent-form-label">
                            <i class="fas fa-money-bill-wave me-2"></i>Client Balance (RWF)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">RWF</span>
                            <input type="number" 
                                   step="0.01" 
                                   min="0"
                                   class="agent-form-control <?php echo isset($validation_errors['balance']) ? 'is-invalid' : ''; ?>" 
                                   id="client_balance" 
                                   name="client_balance" 
                                   value="<?php echo htmlspecialchars($client['balance'] ?? '0'); ?>" 
                                   required>
                        </div>
                        <?php if (isset($validation_errors['balance'])): ?>
                            <div class="invalid-feedback d-block mt-2">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                <?php echo $validation_errors['balance']; ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle me-1"></i>Enter the new balance amount for this client
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-agent btn-agent-success">
                        <i class="fas fa-check-circle me-2"></i> Update Client Balance
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-lg-4">
        <div class="agent-card sticky-top" style="top: 20px;">
            <div class="agent-card-header">
                <i class="fas fa-info-circle"></i>
                Quick Information
            </div>
            <div class="agent-card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-circle fa-2x text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Agent Profile</h5>
                        <p class="mb-0 text-muted small">Manage your personal information</p>
                    </div>
                </div>
                
                <hr>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt fa-2x text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Security</h5>
                        <p class="mb-0 text-muted small">Keep your account secure</p>
                    </div>
                </div>
                
                <hr>
                
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-question-circle fa-2x text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Need Help?</h5>
                        <p class="mb-0 text-muted small">Contact support for assistance</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <a href="../agent_dashboard.php" class="btn-agent btn-agent-outline">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    <a href="../../actions/logout.php" class="btn-agent btn-agent-danger">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show temporary success message
        const originalHTML = event.target.innerHTML;
        event.target.innerHTML = '<i class="fas fa-check"></i> Copied!';
        event.target.classList.remove('btn-outline-secondary');
        event.target.classList.add('btn-success');
        
        setTimeout(function() {
            event.target.innerHTML = originalHTML;
            event.target.classList.remove('btn-success');
            event.target.classList.add('btn-outline-secondary');
        }, 2000);
    });
}
</script>

<?php
$page_content = ob_get_clean();
$agent_page_title = 'Account Settings';

// Include the layout template
include '../agent/_layout.php';
?>
