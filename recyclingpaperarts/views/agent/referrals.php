<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent' || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Database connection
require_once('../../includes/db.php');

// Get the agent's ID
$agent_id = $_SESSION['user_id'];

// Fetch referrals associated with the agent using prepared statement
$referral_query = "SELECT id, phone_number, balance, fname, lname, created_at FROM users WHERE referral_code = (SELECT referral_code FROM users WHERE id = ?) ORDER BY created_at DESC";
$stmt = $conn->prepare($referral_query);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$referral_result = $stmt->get_result();
$referrals = [];
$total_balance = 0;
$total_referrals = 0;

while ($row = $referral_result->fetch_assoc()) {
    $referrals[] = $row;
    $total_balance += $row['balance'];
    $total_referrals++;
}
$stmt->close();

// Build the page content
ob_start(); ?>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="agent-card">
            <div class="agent-card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-users fa-2x text-primary"></i>
                </div>
                <h3 class="mb-2">Total Referrals</h3>
                <div class="display-4 text-primary"><?php echo $total_referrals; ?></div>
                <p class="text-muted mb-0">Active referrals</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="agent-card">
            <div class="agent-card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-wallet fa-2x text-success"></i>
                </div>
                <h3 class="mb-2">Total Balance</h3>
                <div class="display-4 text-success"><?php echo number_format($total_balance); ?></div>
                <p class="text-muted mb-0">RWF</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="agent-card">
            <div class="agent-card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-chart-bar fa-2x text-info"></i>
                </div>
                <h3 class="mb-2">Avg. Balance</h3>
                <div class="display-4 text-info"><?php echo $total_referrals > 0 ? number_format($total_balance / $total_referrals) : 0; ?></div>
                <p class="text-muted mb-0">RWF per referral</p>
            </div>
        </div>
    </div>
</div>

<div class="agent-card">
    <div class="agent-card-header">
        <i class="fas fa-users"></i>
        My Referrals
    </div>
    <div class="agent-card-body">
        <?php if (!empty($referrals)): ?>
            <div class="table-responsive">
                <table class="agent-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone Number</th>
                            <th>Balance</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referrals as $referral): ?>
                            <tr>
                                <td data-label="ID">
                                    <span class="badge bg-secondary">#<?php echo htmlspecialchars($referral['id']); ?></span>
                                </td>
                                <td data-label="Name">
                                    <i class="fas fa-user text-muted me-2"></i>
                                    <?php echo htmlspecialchars(($referral['fname'] ?? '') . ' ' . ($referral['lname'] ?? '')); ?>
                                </td>
                                <td data-label="Phone Number">
                                    <i class="fas fa-phone text-muted me-2"></i>
                                    <?php echo htmlspecialchars($referral['phone_number']); ?>
                                </td>
                                <td data-label="Balance">
                                    <span class="badge bg-success fs-6"><?php echo number_format($referral['balance'], 2); ?> RWF</span>
                                </td>
                                <td data-label="Joined">
                                    <i class="fas fa-calendar text-muted me-2"></i>
                                    <?php echo date('M j, Y', strtotime($referral['created_at'] ?? 'now')); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="agent-empty-state">
                <i class="fas fa-user-plus"></i>
                <h3 class="mb-3">No Referrals Yet</h3>
                <p class="mb-4">You don't have any referrals registered with your referral code yet.</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Share your referral code with others to earn commissions on their investments!
                </div>
            </div>
        <?php endif; ?>
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

<?php
$page_content = ob_get_clean();
$agent_page_title = 'My Referrals';

// Include the layout template
include '../agent/_layout.php';

// Close the database connection
$conn->close();
?>
