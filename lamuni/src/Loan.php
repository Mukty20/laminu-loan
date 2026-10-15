<?php
/**
 * Lamuni — Loan Model
 * ----------------------------------------
 * create, list, retrieve, decide (approve/reject), calculate schedule.
 */

declare(strict_types=1);
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Notification.php';
require_once __DIR__ . '/AuditLog.php';

class Loan
{
    /**
     * Submit a new loan application.
     *
     * @return int|string  New loan ID on success, error message on failure
     */
    public static function apply(int $userId, float $amount, int $durationMonths, string $purpose): int|string
    {
        if (!is_finite($amount) || $amount < 1000 || $amount > 9999999999.99) {
            return 'Loan amount must be between ₦1,000 and ₦9,999,999,999.99.';
        }
        if ($durationMonths < 3 || $durationMonths > 60) {
            return 'Duration must be between 3 and 60 months.';
        }
        $purpose = trim($purpose);
        if ($purpose === '') {
            return 'Please state the purpose of the loan.';
        }
        if (strlen($purpose) > 5000) {
            return 'Purpose must be 5,000 characters or fewer.';
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO loans (user_id, amount, duration_months, purpose, status)
             VALUES (:user_id, :amount, :duration_months, :purpose, 'Pending')"
        );
        $stmt->execute([
            ':user_id'         => $userId,
            ':amount'          => $amount,
            ':duration_months' => $durationMonths,
            ':purpose'         => $purpose,
        ]);
        $loanId = (int)$pdo->lastInsertId();

        Notification::create(
            $userId,
            'Your loan application for ' . self::formatAmount($amount) . ' has been received and is under review.'
        );
        AuditLog::log($userId, "Loan application submitted: " . self::formatAmount($amount) . " for {$durationMonths} months");

        return $loanId;
    }

    /**
     * List all loans belonging to a specific borrower.
     */
    public static function listByUser(int $userId): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT l.*, u.name AS admin_name
               FROM loans l
          LEFT JOIN users u ON u.id = l.decision_by
              WHERE l.user_id = :user_id
           ORDER BY l.applied_at DESC"
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * List ALL loans (admin view), optionally filtered by status.
     */
    public static function listAll(?string $status = null): array
    {
        $pdo = Database::getConnection();
        if ($status) {
            $stmt = $pdo->prepare(
                "SELECT l.*, u.name AS borrower_name, u.email AS borrower_email
                   FROM loans l
                   JOIN users u ON u.id = l.user_id
                  WHERE l.status = :status
               ORDER BY l.applied_at DESC"
            );
            $stmt->execute([':status' => $status]);
        } else {
            $stmt = $pdo->query(
                "SELECT l.*, u.name AS borrower_name, u.email AS borrower_email
                   FROM loans l
                   JOIN users u ON u.id = l.user_id
               ORDER BY l.applied_at DESC"
            );
        }
        return $stmt->fetchAll();
    }

    /** List loans with a positive balance that can accept repayments. */
    public static function listRepayable(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query(
            "SELECT l.*, u.name AS borrower_name, u.email AS borrower_email
               FROM loans l
               JOIN users u ON u.id = l.user_id
              WHERE l.status IN ('Active', 'Approved') AND l.balance > 0
           ORDER BY l.applied_at DESC"
        )->fetchAll();
    }

    /**
     * Get a single loan by ID, with borrower and admin info.
     */
    public static function getById(int $loanId): ?array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT l.*,
                    b.name  AS borrower_name,  b.email AS borrower_email,  b.phone AS borrower_phone,
                    a.name  AS admin_name
               FROM loans l
               JOIN users b ON b.id = l.user_id
          LEFT JOIN users a ON a.id = l.decision_by
              WHERE l.id = :id"
        );
        $stmt->execute([':id' => $loanId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Admin: approve or reject an application.
     *
     * @param string $decision  'Approved' | 'Rejected'
     */
    public static function decide(int $loanId, int $adminId, string $decision, string $note): bool
    {
        if (!in_array($decision, ['Approved', 'Rejected'], true)) {
            return false;
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $lock = $pdo->prepare('SELECT status FROM loans WHERE id = :id FOR UPDATE');
            $lock->execute([':id' => $loanId]);
            if ($lock->fetchColumn() !== 'Pending') {
                $pdo->rollBack();
                return false;
            }

            $loan = self::getById($loanId);
            if (!$loan) {
                $pdo->rollBack();
                return false;
            }

            // Compute repayment schedule on approval
            $newStatus  = $decision;
            $balance    = null;
            $monthlyDue = null;

            if ($decision === 'Approved') {
                $newStatus  = 'Active';
                $balance    = (float)$loan['amount'];
                $monthlyDue = self::calculateMonthlyDue((float)$loan['amount'], (int)$loan['duration_months']);
            }

            $note = trim(strip_tags($note));
            $stmt = $pdo->prepare(
                "UPDATE loans
                    SET status = :status, decision_at = NOW(), decision_by = :admin_id,
                        decision_note = :note, balance = :balance, monthly_due = :monthly_due
                  WHERE id = :id"
            );
            $stmt->execute([
                ':status'      => $newStatus,
                ':admin_id'    => $adminId,
                ':note'        => $note,
                ':balance'     => $balance,
                ':monthly_due' => $monthlyDue,
                ':id'          => $loanId,
            ]);

            $stmt = $pdo->prepare(
                "INSERT INTO approvals (loan_id, admin_id, decision, note)
                 VALUES (:loan_id, :admin_id, :decision, :note)"
            );
            $stmt->execute([
                ':loan_id'  => $loanId,
                ':admin_id' => $adminId,
                ':decision' => $decision,
                ':note'     => $note,
            ]);

            $amount = self::formatAmount((float)$loan['amount']);
            if ($decision === 'Approved') {
                $msg = "Congratulations! Your loan of {$amount} has been approved. Please see your dashboard for repayment details.";
            } else {
                $msg = "Your loan application of {$amount} was not approved. Note: {$note}";
            }
            Notification::create((int)$loan['user_id'], $msg);
            AuditLog::log($adminId, "Admin {$decision} Loan #{$loanId} for user {$loan['borrower_email']}");

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
     * Simple equal-installment monthly due (no interest per spec — demo only).
     */
    public static function calculateMonthlyDue(float $amount, int $months): float
    {
        if ($months <= 0) return $amount;
        return round($amount / $months, 2);
    }

    /**
     * Update outstanding balance after a repayment.
     */
    public static function updateBalance(int $loanId, float $newBalance): void
    {
        $pdo  = Database::getConnection();
        $status = $newBalance <= 0 ? 'Completed' : 'Active';
        $stmt = $pdo->prepare(
            "UPDATE loans SET balance = :balance, status = :status WHERE id = :id"
        );
        $stmt->execute([':balance' => max(0, $newBalance), ':status' => $status, ':id' => $loanId]);
    }

    /**
     * Admin summary counts.
     */
    public static function getSummary(): array
    {
        $pdo = Database::getConnection();
        $row = $pdo->query(
            "SELECT
               COUNT(*) AS total,
               SUM(status = 'Pending')   AS pending,
               SUM(status = 'Approved' OR status = 'Active') AS approved,
               SUM(status = 'Rejected')  AS rejected,
               SUM(status = 'Completed') AS completed,
               COALESCE(SUM(CASE WHEN status IN ('Active') THEN balance ELSE 0 END), 0) AS outstanding_balance,
               COALESCE(SUM(CASE WHEN status IN ('Active','Completed') THEN amount ELSE 0 END), 0) AS total_disbursed
             FROM loans"
        )->fetch();
        return $row;
    }

    public static function formatAmount(float $amount): string
    {
        return '₦' . number_format($amount, 2);
    }
}
