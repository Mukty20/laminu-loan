<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Loan.php';

Auth::requireBorrower();

$userId  = Auth::userId();
$error   = '';
$success = $_SESSION['lamuni_flash_apply'] ?? '';
unset($_SESSION['lamuni_flash_apply']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrfToken($_POST['_csrf'] ?? null)) {
        $error = 'Your session expired. Please reload the page and try again.';
    } else {
        $amount   = (float)($_POST['amount']          ?? 0);
        $duration = (int)($_POST['duration_months']   ?? 0);
        $purpose  = trim($_POST['purpose']             ?? '');

        $result = Loan::apply($userId, $amount, $duration, $purpose);
        if (is_int($result)) {
            $_SESSION['lamuni_flash_apply'] = 'Your loan application has been submitted and is under review.';
            header('Location: /lamuni/public/apply.php');
            exit;
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Apply for a Loan — Lamuni</title>
  <meta name="description" content="Submit a loan application on Lamuni.">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lamuni/public/assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="/lamuni/public/">Lamu<span>ni</span></a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <div class="lang-toggle">
        <button class="lang-btn" data-lang="en">EN</button>
        <button class="lang-btn" data-lang="ha">HA</button>
      </div>
      <a href="/lamuni/public/dashboard.php" class="btn btn-outline-primary btn-sm" data-en="← Dashboard" data-ha="← Allunan Aiki">← Dashboard</a>
      <a href="/lamuni/public/logout.php" class="btn btn-outline-primary btn-sm" data-en="Logout" data-ha="Fita">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">

      <div class="page-header">
        <h1 data-en="Apply for a Loan" data-ha="Nemi Lamuni">Apply for a Loan</h1>
        <p data-en="Fill in the details below. Your application will be reviewed by an admin."
           data-ha="Cika bayanan da ke ƙasa. Ma'aikaci zai duba buƙatar ku.">Fill in the details below. Your application will be reviewed by an admin.</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <?= htmlspecialchars($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <?= htmlspecialchars($success) ?>
          <br><a href="/lamuni/public/dashboard.php" data-en="Back to dashboard →" data-ha="Koma allunan aiki →">Back to dashboard →</a>
        </div>
      <?php else: ?>

      <div class="card p-4">
        <form id="apply-form" method="post" action="">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

          <div class="mb-3">
            <label class="form-label" for="amount" data-en="Loan Amount (₦)" data-ha="Adadin Lamuni (₦)">Loan Amount (₦)</label>
            <input type="number" class="form-control" id="amount" name="amount"
                   value="<?= htmlspecialchars((string)($_POST['amount'] ?? '')) ?>"
                   min="1000" max="9999999999.99" step="0.01" required
                   data-placeholder-en="e.g. 50000"
                   data-placeholder-ha="misali 50000"
                   placeholder="e.g. 50000">
            <div class="form-text" data-en="Minimum ₦1,000" data-ha="Mafi ƙarancin ₦1,000">Minimum ₦1,000</div>
            <div class="invalid-feedback" data-en="Minimum amount is ₦1,000." data-ha="Mafi ƙarancin adadi ₦1,000 ne.">Minimum amount is ₦1,000.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="duration_months" data-en="Repayment Duration (months)" data-ha="Tsawon Biyan Kuɗi (watanni)">Repayment Duration (months)</label>
            <select class="form-select" id="duration_months" name="duration_months" required>
              <option value="" data-en="Select duration" data-ha="Zaɓi tsawon lokaci">Select duration</option>
              <?php foreach ([3,6,9,12,18,24,36,48,60] as $m): ?>
                <option value="<?= $m ?>" data-en="<?= $m ?> months" data-ha="<?= $m ?> watanni"
                  <?= (($_POST['duration_months'] ?? '') == $m ? 'selected' : '') ?>>
                  <?= $m ?> <?= $m === 1 ? 'month' : 'months' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label" for="purpose" data-en="Purpose of Loan" data-ha="Manufar Lamuni">Purpose of Loan</label>
            <textarea class="form-control" id="purpose" name="purpose" rows="4" required
                      maxlength="5000"
                      data-placeholder-en="Briefly describe what you need the loan for"
                      data-placeholder-ha="Taƙaice bayyana dalilan da kuka nemi lamunin"
                      placeholder="Briefly describe what you need the loan for"
                      ><?= htmlspecialchars($_POST['purpose'] ?? '') ?></textarea>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary" data-en="Submit Application" data-ha="Ƙaddamar da Buƙata">Submit Application</button>
          </div>
        </form>
      </div>

      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/lamuni/public/assets/js/app.js?v=2"></script>
</body>
</html>
