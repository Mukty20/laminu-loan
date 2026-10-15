<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Loan.php';

Auth::requireAdmin();

$filterStatus = $_GET['status'] ?? '';
$loans = Loan::listAll($filterStatus ?: null);
$summary = Loan::getSummary();

$statuses = ['', 'Pending', 'Active', 'Approved', 'Rejected', 'Completed'];
$statusLabels = ['' => 'All', 'Pending' => 'Pending', 'Active' => 'Active', 'Approved' => 'Approved', 'Rejected' => 'Rejected', 'Completed' => 'Completed'];

function statusBadge(string $status): string {
    $map = ['Pending' => 'badge-pending', 'Approved' => 'badge-approved', 'Active' => 'badge-active', 'Rejected' => 'badge-rejected', 'Completed' => 'badge-completed'];
    $cls = $map[$status] ?? 'badge-pending';
    return "<span class=\"{$cls}\">{$status}</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports — Lamuni Admin</title>
  <meta name="description" content="Lamuni loan reports — view all loan applications by status.">
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
    <a class="sidebar-link" href="/lamuni/public/admin/repayments.php">💳 Record Repayment</a>
    <a class="sidebar-link active" href="/lamuni/public/admin/reports.php" id="nav-reports">📈 Reports</a>
    <a class="sidebar-link" href="/lamuni/public/admin/audit.php">🔍 Audit Log</a>
  </aside>

  <main class="admin-main">
    <div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <h1>Loan Reports</h1>
        <p>Overview of all loan applications — <?= count($loans) ?> record<?= count($loans) !== 1 ? 's' : '' ?> shown</p>
      </div>
      <!-- Status filter -->
      <form method="get" action="" class="d-flex gap-2 align-items-center">
        <label style="font-size:.875rem;font-weight:500;color:var(--lm-ink-muted)" for="status-filter">Filter:</label>
        <select class="form-select form-select-sm" id="status-filter" name="status" onchange="this.form.submit()">
          <?php foreach ($statuses as $s): ?>
            <option value="<?= $s ?>" <?= ($filterStatus === $s ? 'selected' : '') ?>><?= $statusLabels[$s] ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <!-- Summary strip -->
    <div class="row g-2 mb-4">
      <div class="col-auto"><span class="badge-pending"><?= (int)$summary['pending'] ?> Pending</span></div>
      <div class="col-auto"><span class="badge-active"><?= (int)$summary['approved'] ?> Active/Approved</span></div>
      <div class="col-auto"><span class="badge-rejected"><?= (int)$summary['rejected'] ?> Rejected</span></div>
      <div class="col-auto"><span class="badge-completed"><?= (int)$summary['completed'] ?> Completed</span></div>
      <div class="col-auto" style="margin-left:auto;font-size:.875rem;color:var(--lm-ink-muted)">
        Outstanding: <strong style="color:var(--lm-ink)">₦<?= number_format((float)$summary['outstanding_balance'], 2) ?></strong>
      </div>
    </div>

    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="reports-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Borrower</th>
              <th>Amount</th>
              <th>Duration</th>
              <th>Status</th>
              <th>Balance</th>
              <th>Applied</th>
              <th>Decided</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($loans)): ?>
              <tr><td colspan="9" style="text-align:center;color:var(--lm-ink-muted);padding:2rem">No records found.</td></tr>
            <?php else: ?>
              <?php foreach ($loans as $loan): ?>
                <tr>
                  <td style="color:var(--lm-ink-muted)">#<?= (int)$loan['id'] ?></td>
                  <td>
                    <div style="font-weight:500"><?= htmlspecialchars($loan['borrower_name']) ?></div>
                    <div style="font-size:.78rem;color:var(--lm-ink-muted)"><?= htmlspecialchars($loan['borrower_email']) ?></div>
                  </td>
                  <td><strong><?= Loan::formatAmount((float)$loan['amount']) ?></strong></td>
                  <td><?= (int)$loan['duration_months'] ?> mo</td>
                  <td><?= statusBadge($loan['status']) ?></td>
                  <td>
                    <?= $loan['balance'] !== null
                        ? Loan::formatAmount((float)$loan['balance'])
                        : '<span style="color:var(--lm-ink-muted)">—</span>' ?>
                  </td>
                  <td style="font-size:.8rem;color:var(--lm-ink-muted)"><?= date('d M Y', strtotime($loan['applied_at'])) ?></td>
                  <td style="font-size:.8rem;color:var(--lm-ink-muted)">
                    <?= $loan['decision_at'] ? date('d M Y', strtotime($loan['decision_at'])) : '—' ?>
                  </td>
                  <td>
                    <?php if ($loan['status'] === 'Pending'): ?>
                      <a href="/lamuni/public/admin/review.php?id=<?= (int)$loan['id'] ?>"
                         class="btn btn-primary btn-sm">Review</a>
                    <?php elseif ($loan['status'] === 'Active'): ?>
                      <a href="/lamuni/public/admin/repayments.php?loan_id=<?= (int)$loan['id'] ?>"
                         class="btn btn-outline-primary btn-sm">Repayment</a>
                    <?php else: ?>
                      <span style="color:var(--lm-ink-muted);font-size:.8rem">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/lamuni/public/assets/js/app.js?v=2"></script>
</body>
</html>
