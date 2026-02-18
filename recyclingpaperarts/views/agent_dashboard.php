<?php
// ini_set('display_errors', 2);
// ini_set('display_startup_errors', 2);
// error_reporting(E_ALL);

include 'agent/files.php';

// Build the page content
ob_start(); ?>

<?php if (!empty($message)): ?>
<div class="agent-alert agent-alert-info">
    <i class="fas fa-info-circle"></i>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<!-- Stats Overview -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="agent-card">
            <div class="agent-card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-clock fa-2x text-warning"></i>
                </div>
                <h3 class="mb-2">Pending Recharges</h3>
                <div class="display-4 text-warning"><?php echo count($pending_recharges); ?></div>
                <p class="text-muted mb-0">Awaiting approval</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="agent-card">
            <div class="agent-card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-money-bill-wave fa-2x text-info"></i>
                </div>
                <h3 class="mb-2">Pending Withdrawals</h3>
                <div class="display-4 text-info"><?php echo count($withdrawals_result); ?></div>
                <p class="text-muted mb-0">Awaiting approval</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="agent-card">
            <div class="agent-card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-chart-line fa-2x text-success"></i>
                </div>
                <h3 class="mb-2">Total Actions</h3>
                <div class="display-4 text-success"><?php echo count($pending_recharges) + count($withdrawals_result); ?></div>
                <p class="text-muted mb-0">Pending today</p>
            </div>
        </div>
    </div>
</div>

<!-- Pending Recharges Section -->
<div class="agent-card">
    <div class="agent-card-header">
        <i class="fas fa-clock"></i>
        Pending Recharge Requests
    </div>
    <div class="agent-card-body">
        <?php if (!empty($pending_recharges)): ?>
            <div class="table-responsive">
                <table class="agent-table">
                    <thead>
                        <tr>
                            <th>Client Phone</th>
                            <th>Amount (USD)</th>
                            <th>Amount (RWF)</th>
                            <th>Request Time</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_recharges as $recharge): ?>
                            <tr>
                                <td data-label="Client Phone">
                                    <i class="fas fa-phone text-muted me-2"></i>
                                    <?php echo htmlspecialchars($recharge['client_phone_number']); ?>
                                </td>
                                <td data-label="Amount (USD)">
                                    <span class="badge bg-primary">$<?php echo number_format($recharge['amount_usd'], 2); ?></span>
                                </td>
                                <td data-label="Amount (RWF)">
                                    <span class="badge bg-success"><?php echo number_format($recharge['amount_rwf']); ?> RWF</span>
                                </td>
                                <td data-label="Request Time">
                                    <i class="fas fa-calendar text-muted me-2"></i>
                                    <?php echo date('M j, Y g:i A', strtotime($recharge['request_time'])); ?>
                                </td>
                                <td data-label="Actions">
                                    <form method="POST" action="" class="d-flex gap-sm">
                                        <input type="hidden" name="recharge_id" value="<?php echo $recharge['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn-agent btn-agent-success btn-sm">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn-agent btn-agent-danger btn-sm">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="agent-empty-state">
                <i class="fas fa-inbox"></i>
                <h3 class="mb-3">No Pending Recharges</h3>
                <p class="mb-0">There are no recharge requests awaiting your approval at this time.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pending Withdrawals Section -->
<div class="agent-card">
    <div class="agent-card-header">
        <i class="fas fa-money-bill-wave"></i>
        Pending Withdrawal Requests
    </div>
    <div class="agent-card-body">
        <?php if (!empty($withdrawals_result)): ?>
            <div class="table-responsive">
                <table class="agent-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client Name</th>
                            <th>Amount</th>
                            <th>Net Amount</th>
                            <th>Date</th>
                            <th>Bank Details</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawals_result as $row): ?>
                            <tr>
                                <td data-label="ID">
                                    <span class="badge bg-secondary">#<?php echo $row['id']; ?></span>
                                </td>
                                <td data-label="Client Name">
                                    <i class="fas fa-user text-muted me-2"></i>
                                    <?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?>
                                </td>
                                <td data-label="Amount">
                                    <div>
                                        <small class="d-block text-muted">USD:</small>
                                        <strong>$<?php echo number_format($row['amount_usd'], 2); ?></strong>
                                    </div>
                                    <div>
                                        <small class="d-block text-muted">RWF:</small>
                                        <strong><?php echo number_format($row['amount_rwf']); ?> RWF</strong>
                                    </div>
                                </td>
                                <td data-label="Net Amount">
                                    <span class="badge bg-success fs-6">
                                        <?php echo number_format($row['net_withdrawal']); ?> RWF
                                    </span>
                                    <div class="small text-muted mt-1">(after fees)</div>
                                </td>
                                <td data-label="Date">
                                    <i class="fas fa-calendar text-muted me-2"></i>
                                    <?php echo date('M j, Y', strtotime($row['date'])); ?>
                                    <div class="small text-muted"><?php echo date('g:i A', strtotime($row['date'])); ?></div>
                                </td>
                                <td data-label="Bank Details">
                                    <div>
                                        <small class="d-block text-muted">Bank:</small>
                                        <strong><?php echo htmlspecialchars($row['bank_name'] ?? 'N/A'); ?></strong>
                                    </div>
                                    <div>
                                        <small class="d-block text-muted">Account:</small>
                                        <strong><?php echo htmlspecialchars($row['account_number'] ?? 'N/A'); ?></strong>
                                    </div>
                                    <div>
                                        <small class="d-block text-muted">Holder:</small>
                                        <strong><?php echo htmlspecialchars($row['account_holder'] ?? 'N/A'); ?></strong>
                                    </div>
                                </td>
                                <td data-label="Actions">
                                    <form method="POST" action="" class="d-flex flex-column gap-sm">
                                        <input type="hidden" name="withdrawal_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn-agent btn-agent-success btn-sm">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn-agent btn-agent-danger btn-sm">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="agent-empty-state">
                <i class="fas fa-money-bill-wave"></i>
                <h3 class="mb-3">No Pending Withdrawals</h3>
                <p class="mb-0">There are no withdrawal requests awaiting your approval at this time.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$page_content = ob_get_clean();
$agent_page_title = 'Agent Dashboard';

// Include the layout template
include 'agent/_layout.php';
?>