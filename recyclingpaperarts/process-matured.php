<?php
require_once __DIR__ . '/includes/db.php';

// 1. Fetch matured investments
$stmt = $pdo->query("
    SELECT ui.*, pc.profit_rate, pc.compounding_frequency, pc.cycle, pc.cycle_unit
    FROM user_investments ui
    JOIN products_compound pc ON ui.product_id = pc.id
    WHERE ui.status = 'active' AND ui.maturity_date <= NOW()
");

while ($investment = $stmt->fetch()) {
    // 2. Calculate final payout (capital + profit)
    $payout = calculatePayout(
        $investment['amount'],
        $investment['profit_rate'],
        $investment['cycle'],
        $investment['cycle_unit'],
        $investment['compounding_frequency']
    );

    // 3. Release funds (update user balance or mark as withdrawable)
    $userId = $investment['user_id'];
    $pdo->beginTransaction();
    try {
        // Option A: Add to user's wallet balance (if you have a `wallets` table)
        $pdo->prepare("
            UPDATE users SET balance = balance + ? WHERE id = ?
        ")->execute([$payout, $userId]);

        // Option B: Mark as "completed" and store payout amount (if manual withdrawal)
        // $pdo->prepare("
        //     UPDATE user_investments 
        //     SET status = 'completed', payout_amount = ?
        //     WHERE id = ?
        // ")->execute([$payout, $investment['id']]);

        // Update investment status
        $pdo->prepare("
            UPDATE user_investments SET status = 'completed' WHERE id = ?
        ")->execute([$investment['id']]);

        // Log transaction (optional)
        $pdo->prepare("
            INSERT INTO transactions (user_id, amount, type, description)
            VALUES (?, ?, 'payout', 'Investment matured: +$?')
        ")->execute([$userId, $payout, $payout]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Failed to process investment {$investment['id']}: " . $e->getMessage());
    }
}

echo "Processed matured investments: " . $stmt->rowCount();

/**
 * Calculate capital + profit based on compounding logic.
 */
function calculatePayout($principal, $annualRate, $cycle, $cycleUnit, $compoundingFrequency) {
    $annualRateDecimal = $annualRate / 100;
    $timeInYears = convertCycleToYears($cycle, $cycleUnit);

    switch ($compoundingFrequency) {
        case 'daily':
            $n = 365;
            $payout = $principal * pow(1 + ($annualRateDecimal / $n), $n * $timeInYears);
            break;
        case 'monthly':
            $n = 12;
            $payout = $principal * pow(1 + ($annualRateDecimal / $n), $n * $timeInYears);
            break;
        case 'yearly':
            $payout = $principal * pow(1 + $annualRateDecimal, $timeInYears);
            break;
        default:
            $payout = $principal; // No compounding
    }

    return round($payout, 2);
}

/**
 * Convert cycle (e.g., "5 years") to fractional years for compounding.
 */
function convertCycleToYears($cycle, $cycleUnit) {
    switch ($cycleUnit) {
        case 'days': return $cycle / 365;
        case 'months': return $cycle / 12;
        case 'years': return $cycle;
        default: return 1;
    }
}