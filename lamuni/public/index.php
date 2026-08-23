<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/Auth.php';

// Already logged in — redirect to appropriate dashboard
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: /lamuni/public/admin/dashboard.php');
    } else {
        header('Location: /lamuni/public/dashboard.php');
    }
    exit;
}

$lang = $_COOKIE['lm_lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'ha' ? 'ha' : 'en' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lamuni Online Hausa Loan Management System</title>
  <meta name="description" content="Lamuni is a secure online loan management system with support for Hausa language — apply for loans, track repayments, and manage your finances.">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lamuni/public/assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="/lamuni/public/">Lamu<span>ni</span></a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <!-- Language Toggle -->
      <div class="lang-toggle">
        <button class="lang-btn" data-lang="en">EN</button>
        <button class="lang-btn" data-lang="ha">HA</button>
      </div>
      <a href="/lamuni/public/login.php" class="btn btn-outline-primary btn-sm"
         data-en="Login" data-ha="Shiga">Login</a>
      <a href="/lamuni/public/register.php" class="btn btn-primary btn-sm"
         data-en="Register" data-ha="Yi rajista">Register</a>
    </div>
  </div>
</nav>

<!-- Hero -->
<div class="hero">
  <div class="container">
    <p class="text-uppercase fw-semibold" style="font-size:.78rem;letter-spacing:.1em;color:var(--lm-accent);margin-bottom:.75rem">
      <span data-en="Hausa Loan Management" data-ha="Tsarin Lamuni na Hausanci">Hausa Loan Management</span>
    </p>
    <h1 data-en="Transparent loans,<br>in your language." data-ha="Lamuni mai gaskiya,a cikin yarenku.">Transparent loans,in your language.</h1>
    <p data-en="Apply for a loan, track your repayments, and stay informed all in one place. Available in English and Hausa."
       data-ha="Nemi lamuni, bi diddigin biyan kuɗi, kuma ku kasance a san ku — duka a wuri ɗaya. Yana samuwa a Turanci da Hausa.">
      Apply for a loan, track your repayments, and stay informed all in one place. Available in English and Hausa.
    </p>
    <div class="hero-actions">
      <a href="/lamuni/public/register.php" class="btn btn-primary"
         data-en="Create an Account" data-ha="Ƙirƙiri Asusun">Create an Account</a>
      <a href="/lamuni/public/login.php" class="btn btn-outline-primary"
         data-en="Sign In" data-ha="Shiga">Sign In</a>
    </div>
  </div>
</div>

<!-- Features -->
<section style="padding:3rem 0;border-top:1px solid var(--lm-border)">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 p-4">
          <div style="font-size:1.5rem;margin-bottom:.75rem">📋</div>
          <h5 style="font-weight:600;font-size:.95rem" data-en="Easy Application" data-ha="Sauƙin Nema">Easy Application</h5>
          <p style="font-size:.875rem;color:var(--lm-ink-muted)" data-en="Submit your loan application online in minutes." data-ha="Ƙaddamar da buƙatar lamunin ku ta yanar gizo cikin mintuna.">Submit your loan application online in minutes.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 p-4">
          <div style="font-size:1.5rem;margin-bottom:.75rem">📊</div>
          <h5 style="font-weight:600;font-size:.95rem" data-en="Track Repayments" data-ha="Bi Diddigin Biyan Kuɗi">Track Repayments</h5>
          <p style="font-size:.875rem;color:var(--lm-ink-muted)" data-en="View your balance and payment history at a glance." data-ha="Duba ma'auninku da tarihin biyan kuɗi cikin sauƙi.">View your balance and payment history at a glance.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 p-4">
          <div style="font-size:1.5rem;margin-bottom:.75rem">🔔</div>
          <h5 style="font-weight:600;font-size:.95rem" data-en="Instant Notifications" data-ha="Sanarwa na Kan Lokaci">Instant Notifications</h5>
          <p style="font-size:.875rem;color:var(--lm-ink-muted)" data-en="Get notified when your loan is approved or a repayment is recorded." data-ha="Samu sanarwa lokacin da aka amince da lamunin ku ko an yi rikodi biyan kuɗi.">Get notified when your loan is approved or a repayment is recorded.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<footer style="border-top:1px solid var(--lm-border);padding:1.5rem 0;text-align:center;font-size:.8rem;color:var(--lm-ink-muted)">
  <div class="container">
    &copy; <?= date('Y') ?> Lamuni &mdash;
    <span data-en="Online Hausa Loan Management System" data-ha="Tsarin Lamuni na Hausanci ta yanar gizo">Online Hausa Loan Management System</span>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/lamuni/public/assets/js/app.js"></script>
</body>
</html>
