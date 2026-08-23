-- Lamuni Seed Data
-- NOTE: User accounts (with properly hashed passwords) are inserted by
-- visiting http://localhost/lamuni/public/setup.php once after schema import.
-- This file seeds loans, repayments, notifications, and audit_log.

USE `lamuni`;

-- Seed loans for demo borrower (user id 1, inserted by setup.php)
-- Loan #1: Approved + partially repaid (Active)
INSERT INTO `loans`
  (`id`, `user_id`, `amount`, `duration_months`, `purpose`, `status`,
   `applied_at`, `decision_at`, `decision_by`, `decision_note`, `balance`, `monthly_due`)
VALUES
  (1, 1, 150000.00, 12, 'Purchase of sewing machine and tailoring equipment',
   'Active',
   '2026-07-01 09:00:00', '2026-07-03 11:30:00', 2,
   'Application verified. Collateral not required for this amount.',
   112500.00, 12500.00),

-- Loan #2: Pending (recently applied)
  (2, 1, 75000.00, 6, 'Stock for small provisions store', 'Pending',
   '2026-08-20 14:22:00', NULL, NULL, NULL, NULL, NULL);

-- Seed repayments for Loan #1 (3 of 12 paid)
INSERT INTO `repayments` (`loan_id`, `amount`, `paid_at`, `balance_after`) VALUES
  (1, 12500.00, '2026-08-01 10:05:00', 137500.00),
  (1, 12500.00, '2026-09-01 09:45:00', 125000.00),
  (1, 12500.00, '2026-10-01 10:15:00', 112500.00);

-- Seed approvals record for Loan #1
INSERT INTO `approvals` (`loan_id`, `admin_id`, `decision`, `decided_at`, `note`) VALUES
  (1, 2, 'Approved', '2026-07-03 11:30:00',
   'Application verified. Collateral not required for this amount.');

-- Seed notifications for demo borrower
INSERT INTO `notifications` (`user_id`, `message`, `is_read`, `created_at`) VALUES
  (1, 'Your loan application for ₦150,000.00 has been received and is under review.', 1, '2026-07-01 09:00:00'),
  (1, 'Congratulations! Your loan of ₦150,000.00 has been approved. Please see your dashboard for repayment details.', 1, '2026-07-03 11:30:00'),
  (1, 'Repayment of ₦12,500.00 recorded on your loan. Outstanding balance: ₦137,500.00.', 1, '2026-08-01 10:05:00'),
  (1, 'Repayment of ₦12,500.00 recorded on your loan. Outstanding balance: ₦125,000.00.', 1, '2026-09-01 09:45:00'),
  (1, 'Repayment of ₦12,500.00 recorded on your loan. Outstanding balance: ₦112,500.00.', 1, '2026-10-01 10:15:00'),
  (1, 'Your loan application for ₦75,000.00 has been received and is under review.', 0, '2026-08-20 14:22:00');

-- Seed audit log
INSERT INTO `audit_log` (`user_id`, `action`, `created_at`) VALUES
  (1, 'Borrower registered: demo@lamuni.local', '2026-06-30 08:00:00'),
  (2, 'Admin registered: admin@lamuni.local', '2026-06-30 07:00:00'),
  (1, 'Loan application submitted: ₦150,000.00 for 12 months', '2026-07-01 09:00:00'),
  (2, 'Admin approved Loan #1 for user demo@lamuni.local', '2026-07-03 11:30:00'),
  (2, 'Repayment of ₦12,500.00 recorded for Loan #1 (balance: ₦137,500.00)', '2026-08-01 10:05:00'),
  (2, 'Repayment of ₦12,500.00 recorded for Loan #1 (balance: ₦125,000.00)', '2026-09-01 09:45:00'),
  (2, 'Repayment of ₦12,500.00 recorded for Loan #1 (balance: ₦112,500.00)', '2026-10-01 10:15:00'),
  (1, 'Loan application submitted: ₦75,000.00 for 6 months', '2026-08-20 14:22:00');
