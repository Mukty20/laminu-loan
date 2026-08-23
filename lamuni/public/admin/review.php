<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Loan.php';
require_once __DIR__ . '/../../src/Repayment.php';

Auth::requireAdmin();

$loanId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$loan   = $loanId ? Loan::getById($loanId) : null;

$error   = '';
$success = '';

// Handle approve/reject POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $loan) {
    $decision = $_POST['decision'] ?? '';
    $note     = trim($_POST['note'] ?? '');

    if (!in_array($decision, ['Approved', 'Rejected'], true)) {
        $error = 'Invalid decision.';
    } else {
        $result = Loan::decide((int)$loan['id'], Auth::userId(), $decision, $note);
        if ($result) {
            $success = "Loan #{$loan['id']} has been {$decision}.";
            // Refresh loan data
            $loan = Loan::getById($loanId);
        } else {
            $error = 'Could not process decision. The loan may not be in Pending status.';
        }
    }
}

// Load all pending loans for the selector
$pendingLoans = Loan::listAll('Pending');

function statusBadge(string $status): string {
    $map = [
        'Pending'   => 'badge-pending',
        'Approved'  => 'badge-approved',
        'Active'    => 'badge-active',
        'Rejected'  => 'badge-rejected',
        'Completed' => 'badge-completed',
    ];
    $cls = $map[$status] ?? 'badge-pending';
    return "<span class=\"{$cls}\">{$status}</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Review Application — Lamuni Admin</title>
  <meta name="description" content="Review and decide on a loan application.">
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
    <a class="sidebar-link active" href="/lamuni/public/admin/review.php" id="nav-review">📋 Review Applications</a>
    <a class="sidebar-link" href="/lamuni/public/admin/repayments.php">💳 Record Repayment</a>
    <a class="sidebar-link" href="/lamuni/public/admin/reports.php">📈 Reports</a>
    <a class="sidebar-link" href="/lamuni/public/admin/audit.php">🔍 Audit Log</a>
  </aside>

  <main class="admin-main">
    <div class="page-header">
      <h1>Review Application</h1>
      <p>Evaluate and decide on pending loan applications</p>
    </div>

    <!-- Application Selector -->
    <div class="card mb-4 p-3">
      <form method="get" action="" class="d-flex gap-2 align-items-end flex-wrap">
        <div style="flex:1;min-width:220px">
          <label class="form-label" for="loan-selector">Select a Pending Application</label>
          <select class="form-select" id="loan-selector" name="id">
            <option value="">— Select application —</option>
            <?php foreach ($pendingLoans as $pl): ?>
              <option value="<?= (int)$pl['id'] ?>" <?= ($loanId === (int)$pl['id'] ? 'selected' : '') ?>>
                #<?= (int)$pl['id'] ?> — <?= htmlspecialchars($pl['borrower_name']) ?> —
                <?= Loan::formatAmount((float)$pl['amount']) ?> (<?= (int)$pl['duration_months'] ?> mo)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-outline-primary" id="load-application-btn">Load Application</button>
      </form>
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

    <?php if ($loan): ?>
      <div class="row g-4">

        <!-- Loan Details -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">APPLICATION DETAILS</div>
            <div class="p-4">
              <table style="width:100%;font-size:.875rem;border-collapse:collapse">
                <tr>
                  <td style="color:var(--lm-ink-muted);padding:.45rem 0;width:40%">Loan ID</td>
                  <td><strong>#<?= (int)$loan['id'] ?></strong></td>
                </tr>
                <tr>
                  <td style="color:var(--lm-ink-muted);padding:.45rem 0">Borrower</td>
                  <td><strong><?= htmlspecialchars($loan['borrower_name']) ?></strong></td>
                </tr>
                <tr>
                  <td style="color:var(--lm-ink-muted);padding:.45rem 0">Email</td>
                  <td><?= htmlspecialchars($loan['borrower_email']) ?></td>
                </tr>
                <tr>
                  <td style="color:var(--lm-ink-muted);padding:.45rem 0">Phone</td>
                  <td><?= htmlspecialchars($loan['borrower_phone']) ?></td>
                </tr>
                <tr>
                  <td style="color:var(--lm-ink-muted);padding:.45rem 0">Amount</td>
                  <td><strong style="font-size:1.05rem"><?= Loan::formatAmount((float)$loan['amount']) ?></strong></td>
                </tr>
                <tr>
                  <td style="color:var(--lm-ink-muted);padding:.45rem 0">Duration</td>
                  <td><?= (int)$loan['duration_months'] ?> months</td>
                </tr>
                <tr>
                  <td style="color:var(--lm-ink-muted);padding:.45rem 0">Monthly Due</td>
                  <td><?= Loan::formatAmount(Loan::calculateMonthlyDue((float)$loan['amount'], (int)$loan['duration_months'])) ?> (est.)</td>
                </tr>
                <tr>
                  <td style="color:var(--lm-ink-muted);padding:.45rem 0">Applied</td>
                  <td><?= date('d M Y H:i', strtotime($loan['applied_at'])) ?></td>
                </tr>
                <tr>
                  <td style="color:var(--lm-ink-muted);padding:.45rem 0">Status</td>
                  <td><?= statusBadge($loan['status']) ?></td>
                </tr>
                <tr>
                  <td style="color:var(--lm-ink-muted);padding:.45rem 0;vertical-align:top">Purpose</td>
                  <td style="line-height:1.5"><?= nl2br(htmlspecialchars($loan['purpose'])) ?></td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Decision Form -->
        <div class="col-lg-6">
          <?php if ($loan['status'] === 'Pending'): ?>
          <div class="card">
            <div class="card-header">MAKE A DECISION</div>
            <div class="p-4">
              <form method="post" action="?id=<?= (int)$loan['id'] ?>">
                <div class="mb-3">
                  <label class="form-label" for="decision">Decision</label>
                  <select class="form-select" id="decision" name="decision" required>
                    <option value="">— Select decision —</option>
                    <option value="Approved">✅ Approve</option>
                    <option value="Rejected">❌ Reject</option>
                  </select>
                </div>
                <div class="mb-4">
                  <label class="form-label" for="note">Note / Reason</label>
                  <textarea class="form-control" id="note" name="note" rows="4"
                            placeholder="Add a note to be sent to the borrower (optional for approval, recommended for rejection)"></textarea>
                </div>
                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-primary" id="submit-decision-btn"
                          data-confirm="Are you sure you want to submit this decision? This action cannot be undone.">
                    Submit Decision
                  </button>
                  <a href="/lamuni/public/admin/review.php" class="btn btn-outline-primary">Cancel</a>
                </div>
              </form>
            </div>
          </div>
          <?php else: ?>
          <div class="card p-4">
            <p style="font-size:.875rem;color:var(--lm-ink-muted)">This application has already been decided.</p>
            <?php if ($loan['decision_at']): ?>
              <p style="font-size:.875rem">
                <strong>Decision:</strong> <?= statusBadge($loan['status']) ?><br>
                <strong>By:</strong> <?= htmlspecialchars($loan['admin_name'] ?? 'N/A') ?><br>
                <strong>On:</strong> <?= date('d M Y H:i', strtotime($loan['decision_at'])) ?><br>
                <?php if ($loan['decision_note']): ?>
                  <strong>Note:</strong> <?= htmlspecialchars($loan['decision_note']) ?>
                <?php endif; ?>
              </p>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

      </div>

    <?php elseif ($loanId): ?>
      <div class="alert alert-danger">Loan application #<?= $loanId ?> not found.</div>
    <?php else: ?>
      <div class="card p-4" style="text-align:center;color:var(--lm-ink-muted);font-size:.875rem">
        Select a pending application above to review it.
      </div>
    <?php endif; ?>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/lamuni/public/assets/js/app.js"></script>
</body>
</html>
