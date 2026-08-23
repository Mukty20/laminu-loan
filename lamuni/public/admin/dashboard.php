<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Loan.php';
require_once __DIR__ . '/../../src/AuditLog.php';

Auth::requireAdmin();

$summary = Loan::getSummary();
$pending = Loan::listAll('Pending');
$recentAudit = AuditLog::getAll(10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard — Lamuni</title>
  <meta name="description" content="Lamuni administrator dashboard — manage loan applications and system activity.">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lamuni/public/assets/css/style.css">
</head>
<body>

<!-- Navbar -->
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

  <!-- Sidebar -->
  <aside class="sidebar">
    <a class="sidebar-link active" href="/lamuni/public/admin/dashboard.php" id="nav-dashboard">📊 Dashboard</a>
    <a class="sidebar-link" href="/lamuni/public/admin/review.php" id="nav-review">📋 Review Applications</a>
    <a class="sidebar-link" href="/lamuni/public/admin/repayments.php" id="nav-repayments">💳 Record Repayment</a>
    <a class="sidebar-link" href="/lamuni/public/admin/reports.php" id="nav-reports">📈 Reports</a>
    <a class="sidebar-link" href="/lamuni/public/admin/audit.php" id="nav-audit">🔍 Audit Log</a>
  </aside>

  <!-- Main -->
  <main class="admin-main">
    <div class="page-header">
      <h1>Dashboard</h1>
      <p>System overview — <?= date('d F Y') ?></p>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
          <div class="stat-value"><?= number_format((int)$summary['total']) ?></div>
          <div class="stat-label">Total Applications</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="border-left-color:var(--lm-pending)">
          <div class="stat-value" style="color:var(--lm-pending)"><?= number_format((int)$summary['pending']) ?></div>
          <div class="stat-label">Pending</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="border-left-color:var(--lm-approved)">
          <div class="stat-value" style="color:var(--lm-approved)"><?= number_format((int)$summary['approved']) ?></div>
          <div class="stat-label">Approved / Active</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="border-left-color:var(--lm-rejected)">
          <div class="stat-value" style="color:var(--lm-rejected)"><?= number_format((int)$summary['rejected']) ?></div>
          <div class="stat-label">Rejected</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="border-left-color:var(--lm-completed)">
          <div class="stat-value" style="color:var(--lm-completed)"><?= number_format((int)$summary['completed']) ?></div>
          <div class="stat-label">Completed</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="border-left-color:#1a1a1a">
          <div class="stat-value" style="font-size:1.1rem">₦<?= number_format((float)$summary['total_disbursed'], 0) ?></div>
          <div class="stat-label">Total Disbursed</div>
        </div>
      </div>
    </div>

    <!-- Outstanding Balance Banner -->
    <div style="background:var(--lm-accent-light);border:1px solid var(--lm-accent);border-radius:var(--lm-radius);padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center">
      <div>
        <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--lm-accent)">Outstanding Balance (Active Loans)</div>
        <div style="font-size:1.5rem;font-weight:700;color:var(--lm-ink)">₦<?= number_format((float)$summary['outstanding_balance'], 2) ?></div>
      </div>
      <a href="/lamuni/public/admin/reports.php" class="btn btn-primary btn-sm">View All Loans →</a>
    </div>

    <div class="row g-4">

      <!-- Pending Applications -->
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>PENDING APPLICATIONS</span>
            <span class="badge-pending"><?= count($pending) ?></span>
          </div>
          <?php if (empty($pending)): ?>
            <div class="p-4" style="font-size:.875rem;color:var(--lm-ink-muted)">No pending applications. 🎉</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0" id="pending-table">
                <thead>
                  <tr>
                    <th>Borrower</th>
                    <th>Amount</th>
                    <th>Duration</th>
                    <th>Applied</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pending as $loan): ?>
                    <tr>
                      <td>
                        <div style="font-weight:500"><?= htmlspecialchars($loan['borrower_name']) ?></div>
                        <div style="font-size:.78rem;color:var(--lm-ink-muted)"><?= htmlspecialchars($loan['borrower_email']) ?></div>
                      </td>
                      <td><strong><?= Loan::formatAmount((float)$loan['amount']) ?></strong></td>
                      <td><?= (int)$loan['duration_months'] ?> mo</td>
                      <td style="font-size:.8rem;color:var(--lm-ink-muted)"><?= date('d M Y', strtotime($loan['applied_at'])) ?></td>
                      <td>
                        <a href="/lamuni/public/admin/review.php?id=<?= (int)$loan['id'] ?>"
                           class="btn btn-primary btn-sm" id="review-loan-<?= (int)$loan['id'] ?>">Review</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Recent Audit Log -->
      <div class="col-lg-5">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>RECENT ACTIVITY</span>
            <a href="/lamuni/public/admin/audit.php" style="font-size:.78rem;color:var(--lm-accent)">View all →</a>
          </div>
          <?php if (empty($recentAudit)): ?>
            <div class="p-3" style="font-size:.875rem;color:var(--lm-ink-muted)">No activity yet.</div>
          <?php else: ?>
            <?php foreach ($recentAudit as $entry): ?>
              <div style="padding:.6rem 1rem;border-bottom:1px solid var(--lm-border);font-size:.8rem">
                <div style="color:var(--lm-ink)"><?= htmlspecialchars($entry['action']) ?></div>
                <div style="color:var(--lm-ink-muted);margin-top:.15rem">
                  <?= $entry['user_name'] ? htmlspecialchars($entry['user_name']) : 'System' ?>
                  · <?= date('d M Y H:i', strtotime($entry['created_at'])) ?>
                </div>
              </div>
            <?php endforeach; ?>
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
