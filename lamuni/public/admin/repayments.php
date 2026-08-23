<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Loan.php';
require_once __DIR__ . '/../../src/Repayment.php';

Auth::requireAdmin();

$error   = '';
$success = '';

// Handle repayment POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = (int)($_POST['loan_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);

    $result = Repayment::record($loanId, $amount, Auth::userId());
    if ($result === true) {
        $success = "Repayment of " . Loan::formatAmount($amount) . " recorded for Loan #{$loanId}.";
    } else {
        $error = $result;
    }
}

// Active loans for the selector
$activeLoans = Loan::listAll('Active');
$selectedLoanId = (int)($_GET['loan_id'] ?? $_POST['loan_id'] ?? 0);
$selectedLoan   = $selectedLoanId ? Loan::getById($selectedLoanId) : null;
$repaymentHistory = $selectedLoan ? Repayment::listByLoan($selectedLoanId) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Record Repayment — Lamuni Admin</title>
  <meta name="description" content="Record a loan repayment for an active borrower.">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lamuni/public/assets/css/style.css">
</head>
<body>

<nav class="navbar">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="/lamuni/public/">Lamu<span>ni</span></a>
    <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--lm-accent);margin-left:.5rem">Admin</div>
    <div class="ms-auto d-flex align-items-center gap-3">
      <span style="font-size:.875rem;color:var(--lm-ink-muted)"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
      <a href="/lamuni/public/logout.php" class="btn btn-outline-primary btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="admin-layout">

  <aside class="sidebar">
    <a class="sidebar-link" href="/lamuni/public/admin/dashboard.php">📊 Dashboard</a>
    <a class="sidebar-link" href="/lamuni/public/admin/review.php">📋 Review Applications</a>
    <a class="sidebar-link active" href="/lamuni/public/admin/repayments.php" id="nav-repayments">💳 Record Repayment</a>
    <a class="sidebar-link" href="/lamuni/public/admin/reports.php">📈 Reports</a>
    <a class="sidebar-link" href="/lamuni/public/admin/audit.php">🔍 Audit Log</a>
  </aside>

  <main class="admin-main">
    <div class="page-header">
      <h1>Record Repayment</h1>
      <p>Log a payment received from a borrower against an active loan</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4">

      <!-- Loan Selector + Repayment Form -->
      <div class="col-lg-5">
        <div class="card">
          <div class="card-header">LOAN SELECTOR</div>
          <div class="p-4">
            <form method="get" action="" class="mb-4">
              <label class="form-label" for="loan-selector">Select Active Loan</label>
              <select class="form-select mb-2" id="loan-selector" name="loan_id">
                <option value="">— Select a loan —</option>
                <?php foreach ($activeLoans as $al): ?>
                  <option value="<?= (int)$al['id'] ?>" <?= ($selectedLoanId === (int)$al['id'] ? 'selected' : '') ?>>
                    #<?= (int)$al['id'] ?> — <?= htmlspecialchars($al['borrower_name']) ?> —
                    <?= Loan::formatAmount((float)$al['amount']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-outline-primary btn-sm" id="load-loan-btn">Load Loan</button>
            </form>

            <?php if ($selectedLoan): ?>
              <div style="background:var(--lm-bg);border:1px solid var(--lm-border);border-radius:var(--lm-radius);padding:1rem;margin-bottom:1.25rem;font-size:.875rem">
                <div class="mb-1"><strong><?= htmlspecialchars($selectedLoan['borrower_name']) ?></strong></div>
                <div style="color:var(--lm-ink-muted)"><?= htmlspecialchars($selectedLoan['borrower_email']) ?></div>
                <hr style="border-color:var(--lm-border);margin:.75rem 0">
                <div class="d-flex justify-content-between">
                  <span style="color:var(--lm-ink-muted)">Loan Amount</span>
                  <strong><?= Loan::formatAmount((float)$selectedLoan['amount']) ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                  <span style="color:var(--lm-ink-muted)">Monthly Due</span>
                  <strong><?= Loan::formatAmount((float)$selectedLoan['monthly_due']) ?></strong>
                </div>
                <div class="d-flex justify-content-between mt-1">
                  <span style="color:var(--lm-ink-muted)">Outstanding Balance</span>
                  <strong style="color:var(--lm-accent)"><?= Loan::formatAmount((float)$selectedLoan['balance']) ?></strong>
                </div>
              </div>

              <form method="post" action="">
                <input type="hidden" name="loan_id" value="<?= (int)$selectedLoan['id'] ?>">
                <div class="mb-3">
                  <label class="form-label" for="repay-amount">Payment Amount (₦)</label>
                  <input type="number" class="form-control" id="repay-amount" name="amount"
                         step="0.01" min="1"
                         max="<?= (float)$selectedLoan['balance'] ?>"
                         value="<?= (float)$selectedLoan['monthly_due'] ?>"
                         required placeholder="Enter amount paid">
                  <div class="form-text">Monthly due: <?= Loan::formatAmount((float)$selectedLoan['monthly_due']) ?></div>
                </div>
                <button type="submit" class="btn btn-primary w-100" id="submit-repayment-btn"
                        data-confirm="Confirm recording this repayment?">
                  Record Repayment
                </button>
              </form>
            <?php elseif (count($activeLoans) === 0): ?>
              <p style="font-size:.875rem;color:var(--lm-ink-muted)">No active loans to record repayments for.</p>
            <?php else: ?>
              <p style="font-size:.875rem;color:var(--lm-ink-muted)">Select a loan above to record a repayment.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Repayment History -->
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header">REPAYMENT HISTORY<?= $selectedLoan ? ' — Loan #' . (int)$selectedLoan['id'] : '' ?></div>
          <?php if (empty($repaymentHistory)): ?>
            <div class="p-4" style="font-size:.875rem;color:var(--lm-ink-muted)">
              <?= $selectedLoan ? 'No repayments recorded for this loan yet.' : 'Select a loan to view repayment history.' ?>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table mb-0" id="repayment-history-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Amount Paid</th>
                    <th>Balance After</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($repaymentHistory as $i => $r): ?>
                    <tr>
                      <td style="color:var(--lm-ink-muted)"><?= count($repaymentHistory) - $i ?></td>
                      <td><?= date('d M Y H:i', strtotime($r['paid_at'])) ?></td>
                      <td><strong><?= Loan::formatAmount((float)$r['amount']) ?></strong></td>
                      <td style="color:var(--lm-ink-muted)"><?= Loan::formatAmount((float)$r['balance_after']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/lamuni/public/assets/js/app.js"></script>
</body>
</html>
