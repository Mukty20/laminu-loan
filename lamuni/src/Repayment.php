<?php
/**
 * Lamuni — Repayment Model
 */

declare(strict_types=1);
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Loan.php';
require_once __DIR__ . '/Notification.php';
require_once __DIR__ . '/AuditLog.php';

class Repayment
{
    /**
     * Record a repayment against a loan.
     *
     * @return true|string  true on success, error message on failure
     */
    public static function record(int $loanId, float $amount, int $adminId): true|string
    {
        $loan = Loan::getById($loanId);
        if (!$loan) {
            return 'Loan not found.';
        }
        if (!in_array($loan['status'], ['Active', 'Approved'], true)) {
            return 'Repayments can only be recorded for active loans.';
        }
        if ($amount <= 0) {
            return 'Repayment amount must be greater than zero.';
        }

        $currentBalance = (float)$loan['balance'];
        $newBalance     = max(0, $currentBalance - $amount);

        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO repayments (loan_id, amount, balance_after)
             VALUES (:loan_id, :amount, :balance_after)"
        );
        $stmt->execute([
            ':loan_id'      => $loanId,
            ':amount'       => $amount,
            ':balance_after' => $newBalance,
        ]);

        // Update loan balance (and status if fully paid)
        Loan::updateBalance($loanId, $newBalance);

        // Notify borrower
        $amtFmt = Loan::formatAmount($amount);
        $balFmt = Loan::formatAmount($newBalance);
        Notification::create(
            (int)$loan['user_id'],
            "Repayment of {$amtFmt} recorded on your loan. Outstanding balance: {$balFmt}."
        );

        AuditLog::log(
            $adminId,
            "Repayment of {$amtFmt} recorded for Loan #{$loanId} (balance: {$balFmt})"
        );

        return true;
    }

    /**
     * List all repayments for a given loan.
     */
    public static function listByLoan(int $loanId): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM repayments WHERE loan_id = :loan_id ORDER BY paid_at DESC"
        );
        $stmt->execute([':loan_id' => $loanId]);
        return $stmt->fetchAll();
    }
}
