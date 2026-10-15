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
        if (!is_finite($amount) || $amount <= 0 || $amount > 9999999999.99) {
            return 'Repayment amount must be between ₦0.01 and ₦9,999,999,999.99.';
        }

        $amountCents = (int)round($amount * 100);
        if ($amountCents <= 0) {
            return 'Repayment amount must be at least ₦0.01.';
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'SELECT user_id, status, balance FROM loans WHERE id = :loan_id FOR UPDATE'
            );
            $stmt->execute([':loan_id' => $loanId]);
            $loan = $stmt->fetch();
            if (!$loan) {
                $pdo->rollBack();
                return 'Loan not found.';
            }
            if (!in_array($loan['status'], ['Active', 'Approved'], true) || $loan['balance'] === null) {
                $pdo->rollBack();
                return 'Repayments can only be recorded for active loans.';
            }

            $balanceCents = (int)round((float)$loan['balance'] * 100);
            if ($amountCents > $balanceCents) {
                $pdo->rollBack();
                return 'Repayment cannot exceed the outstanding balance.';
            }

            $newBalanceCents = $balanceCents - $amountCents;
            $recordedAmount = $amountCents / 100;
            $newBalance = $newBalanceCents / 100;

            $stmt = $pdo->prepare(
                "INSERT INTO repayments (loan_id, amount, balance_after)
                 VALUES (:loan_id, :amount, :balance_after)"
            );
            $stmt->execute([
                ':loan_id'       => $loanId,
                ':amount'        => $recordedAmount,
                ':balance_after' => $newBalance,
            ]);

            $status = $newBalanceCents === 0 ? 'Completed' : 'Active';
            $stmt = $pdo->prepare(
                'UPDATE loans SET balance = :balance, status = :status WHERE id = :loan_id'
            );
            $stmt->execute([
                ':balance' => $newBalance,
                ':status'  => $status,
                ':loan_id' => $loanId,
            ]);

            $amtFmt = Loan::formatAmount($recordedAmount);
            $balFmt = Loan::formatAmount($newBalance);
            Notification::create(
                (int)$loan['user_id'],
                "Repayment of {$amtFmt} recorded on your loan. Outstanding balance: {$balFmt}."
            );
            AuditLog::log(
                $adminId,
                "Repayment of {$amtFmt} recorded for Loan #{$loanId} (balance: {$balFmt})"
            );

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
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
