<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/AuditLog.php';

Auth::requireAdmin();

$entries = AuditLog::getAll(200);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Audit Log — Lamuni Admin</title>
  <meta name="description" content="Chronological audit log of all system actions in Lamuni.">
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
    <a class="sidebar-link" href="/lamuni/public/admin/reports.php">📈 Reports</a>
    <a class="sidebar-link active" href="/lamuni/public/admin/audit.php" id="nav-audit">🔍 Audit Log</a>
  </aside>

  <main class="admin-main">
    <div class="page-header">
      <h1>Audit Log</h1>
      <p>Chronological record of all key system actions — <?= count($entries) ?> entries</p>
    </div>

    <div class="card">
      <div class="table-responsive">
        <table class="table mb-0" id="audit-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Timestamp</th>
              <th>User</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($entries)): ?>
              <tr><td colspan="4" style="text-align:center;color:var(--lm-ink-muted);padding:2rem">No audit entries yet.</td></tr>
            <?php else: ?>
              <?php foreach ($entries as $i => $e): ?>
                <tr>
                  <td style="color:var(--lm-ink-muted);font-size:.8rem"><?= count($entries) - $i ?></td>
                  <td style="font-size:.8rem;white-space:nowrap;color:var(--lm-ink-muted)">
                    <?= date('d M Y', strtotime($e['created_at'])) ?><br>
                    <span style="font-family:monospace"><?= date('H:i:s', strtotime($e['created_at'])) ?></span>
                  </td>
                  <td>
                    <?php if ($e['user_name']): ?>
                      <div style="font-weight:500;font-size:.875rem"><?= htmlspecialchars($e['user_name']) ?></div>
                      <div style="font-size:.78rem;color:var(--lm-ink-muted)"><?= htmlspecialchars($e['user_email'] ?? '') ?></div>
                    <?php else: ?>
                      <span style="color:var(--lm-ink-muted);font-size:.875rem">System</span>
                    <?php endif; ?>
                  </td>
                  <td style="font-size:.875rem"><?= htmlspecialchars($e['action']) ?></td>
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
