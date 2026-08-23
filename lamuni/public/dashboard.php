<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Loan.php';
require_once __DIR__ . '/../src/Repayment.php';
require_once __DIR__ . '/../src/Notification.php';

Auth::requireBorrower();

$userId = Auth::userId();
$loans  = Loan::listByUser($userId);
$notifs = Notification::getForUser($userId, 10);
$unread = Notification::countUnread($userId);

// Mark all read when viewing dashboard
Notification::markAllRead($userId);

// Get repayments for Active loans
$repayments = [];
foreach ($loans as $loan) {
    if (in_array($loan['status'], ['Active', 'Completed'])) {
        $repayments[$loan['id']] = Repayment::listByLoan((int)$loan['id']);
    }
}

// BVN masked display
$bvn = $_SESSION['user_bvn'] ?? null;
$bvnDisplay = $bvn ? str_repeat('•', 7) . substr($bvn, -4) : 'N/A';

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
  <title>Dashboard — Lamuni</title>
  <meta name="description" content="View your Lamuni loan applications, repayments, and notifications.">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lamuni/public/assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="/lamuni/public/">Lamu<span>ni</span></a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <div class="lang-toggle">
        <button class="lang-btn" data-lang="en">EN</button>
        <button class="lang-btn" data-lang="ha">HA</button>
      </div>
      <?php if ($unread > 0): ?>
        <span style="font-size:.875rem" data-en="Notifications" data-ha="Sanarwa">Notifications</span>
        <span class="notif-badge"><?= $unread ?></span>
      <?php endif; ?>
      <span style="font-size:.875rem;color:var(--lm-ink-muted)"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
      <a href="/lamuni/public/logout.php" class="btn btn-outline-primary btn-sm" data-en="Logout" data-ha="Fita">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">

  <!-- Page Header -->
  <div class="page-header d-flex justify-content-between align-items-center">
    <div>
      <h1 data-en="My Dashboard" data-ha="Allunan Aikina">My Dashboard</h1>
      <p data-en="Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>" data-ha="Barka da dawowa, <?= htmlspecialchars($_SESSION['user_name']) ?>">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
    </div>
    <a href="/lamuni/public/apply.php" class="btn btn-primary btn-sm" data-en="Apply for Loan" data-ha="Nemi Lamuni">Apply for Loan</a>
  </div>

  <div class="row g-4">

    <!-- Loans -->
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header" data-en="MY LOANS" data-ha="LAMUNINA">MY LOANS</div>
        <div class="p-0">
          <?php if (empty($loans)): ?>
            <div class="p-4" style="color:var(--lm-ink-muted);font-size:.875rem">
              <span data-en="No loan applications yet." data-ha="Babu buƙatun lamuni tukuna.">No loan applications yet.</span>
              <a href="/lamuni/public/apply.php" data-en=" Apply now." data-ha=" Nema yanzu."> Apply now.</a>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0" id="loans-table">
                <thead>
                  <tr>
                    <th data-en="Amount" data-ha="Adadin Kuɗi">Amount</th>
                    <th data-en="Duration" data-ha="Tsawon Lokaci">Duration</th>
                    <th data-en="Status" data-ha="Matsayi">Status</th>
                    <th data-en="Balance" data-ha="Ma'auni">Balance</th>
                    <th data-en="Applied" data-ha="An Nema">Applied</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($loans as $loan): ?>
                    <tr>
                      <td><strong><?= Loan::formatAmount((float)$loan['amount']) ?></strong></td>
                      <td><?= (int)$loan['duration_months'] ?> <span data-en="mo" data-ha="wata">mo</span></td>
                      <td><?= statusBadge($loan['status']) ?></td>
                      <td>
                        <?php if ($loan['balance'] !== null): ?>
                          <?= Loan::formatAmount((float)$loan['balance']) ?>
                        <?php else: ?>
                          <span style="color:var(--lm-ink-muted)">—</span>
                        <?php endif; ?>
                      </td>
                      <td style="color:var(--lm-ink-muted);font-size:.8rem"><?= date('d M Y', strtotime($loan['applied_at'])) ?></td>
                    </tr>
                    <?php if (!empty($repayments[$loan['id']])): ?>
                      <tr>
                        <td colspan="5" style="background:var(--lm-bg);padding:0">
                          <div style="padding:.75rem 1rem">
                            <p style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--lm-ink-muted);margin-bottom:.5rem"
                               data-en="REPAYMENT HISTORY" data-ha="TARIHIN BIYAN KUƊI">REPAYMENT HISTORY</p>
                            <table style="width:100%;font-size:.8125rem">
                              <tr style="color:var(--lm-ink-muted)">
                                <td data-en="Date" data-ha="Kwanan wata">Date</td>
                                <td data-en="Paid" data-ha="An biya">Paid</td>
                                <td data-en="Balance After" data-ha="Ma'auni bayan">Balance After</td>
                              </tr>
                              <?php foreach (array_slice($repayments[$loan['id']], 0, 5) as $r): ?>
                                <tr>
                                  <td><?= date('d M Y', strtotime($r['paid_at'])) ?></td>
                                  <td><?= Loan::formatAmount((float)$r['amount']) ?></td>
                                  <td><?= Loan::formatAmount((float)$r['balance_after']) ?></td>
                                </tr>
                              <?php endforeach; ?>
                            </table>
                          </div>
                        </td>
                      </tr>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div><!-- /col -->

    <!-- Right sidebar: Account + Notifications -->
    <div class="col-lg-4">

      <!-- Account Info -->
      <div class="card mb-4">
        <div class="card-header" data-en="ACCOUNT INFO" data-ha="BAYANAN ASUSUN">ACCOUNT INFO</div>
        <div class="p-3" style="font-size:.875rem">
          <div class="mb-2">
            <span style="color:var(--lm-ink-muted)" data-en="Name" data-ha="Suna">Name</span><br>
            <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
          </div>
          <div class="mb-2">
            <span style="color:var(--lm-ink-muted)" data-en="BVN (masked)" data-ha="BVN (boye)">BVN (masked)</span><br>
            <span class="bvn-masked"><?= htmlspecialchars($bvnDisplay) ?></span>
          </div>
        </div>
      </div>

      <!-- Notifications -->
      <div class="card">
        <div class="card-header" data-en="NOTIFICATIONS" data-ha="SANARWA">NOTIFICATIONS</div>
        <?php if (empty($notifs)): ?>
          <div class="p-3" style="font-size:.875rem;color:var(--lm-ink-muted)" data-en="No notifications." data-ha="Babu sanarwa.">No notifications.</div>
        <?php else: ?>
          <?php foreach ($notifs as $n): ?>
            <div class="notif-item<?= $n['is_read'] ? '' : ' unread' ?>">
              <div><?= htmlspecialchars($n['message']) ?></div>
              <div class="notif-time"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div><!-- /col -->

  </div><!-- /row -->
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/lamuni/public/assets/js/app.js"></script>
</body>
</html>
