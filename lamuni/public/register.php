<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/Auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in
if (!empty($_SESSION['user_id'])) {
    header('Location: /lamuni/public/dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $bvn      = trim($_POST['bvn']      ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if ($password !== $confirm) {
        $error = 'Passwords do not match. / Kalmomin sirri ba su dace ba.';
    } else {
        $result = Auth::register($name, $email, $phone, $bvn ?: null, $password);
        if ($result === true) {
            $success = 'Account created successfully! You can now sign in.';
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
  <title>Register — Lamuni</title>
  <meta name="description" content="Create a Lamuni account to apply for loans and manage your repayments.">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lamuni/public/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">

    <!-- Logo + Lang toggle -->
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <div class="auth-logo">Lamu<span>ni</span></div>
        <div class="auth-subtitle" data-en="Create your borrower account" data-ha="Ƙirƙiri asusun ɗan aro">Create your borrower account</div>
      </div>
      <div class="lang-toggle">
        <button class="lang-btn" data-lang="en">EN</button>
        <button class="lang-btn" data-lang="ha">HA</button>
      </div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success" role="alert">
        <?= htmlspecialchars($success) ?>
        <br><a href="/lamuni/public/login.php" data-en="Sign in now →" data-ha="Shiga yanzu →">Sign in now →</a>
      </div>
    <?php else: ?>

    <form id="register-form" method="post" action="" novalidate>

      <!-- Full Name -->
      <div class="mb-3">
        <label class="form-label" for="name" data-en="Full Name" data-ha="Sunan Cikakke">Full Name</label>
        <input type="text" class="form-control" id="name" name="name"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
               required autocomplete="name"
               data-placeholder-en="Enter your full name"
               data-placeholder-ha="Shigar da sunan cikakkensa"
               placeholder="Enter your full name">
      </div>

      <!-- Email -->
      <div class="mb-3">
        <label class="form-label" for="email" data-en="Email Address" data-ha="Adireshin Imel">Email Address</label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               required autocomplete="email"
               placeholder="e.g. name@example.com">
      </div>

      <!-- Phone -->
      <div class="mb-3">
        <label class="form-label" for="phone" data-en="Phone Number" data-ha="Lambar Waya">Phone Number</label>
        <input type="tel" class="form-control" id="phone" name="phone"
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
               pattern="[0-9]{11}" maxlength="11" required
               data-placeholder-en="11-digit number (e.g. 07012345678)"
               data-placeholder-ha="Lambobi 11 (misali 07012345678)"
               placeholder="11-digit number (e.g. 07012345678)">
        <div class="invalid-feedback" id="phone-error">Phone number must be exactly 11 digits.</div>
        <div class="form-text" data-en="Demo field only — no SMS verification." data-ha="Filin gwaji kawai — babu tabbatar da SMS.">Demo field only — no SMS verification.</div>
      </div>

      <!-- BVN -->
      <div class="mb-3">
        <label class="form-label" for="bvn" data-en="BVN (demo — 11 digits, not verified)" data-ha="BVN (gwaji — lambobi 11, ba a tabbatar)">BVN (demo — 11 digits, not verified)</label>
        <input type="text" class="form-control" id="bvn" name="bvn"
               value="<?= htmlspecialchars($_POST['bvn'] ?? '') ?>"
               pattern="[0-9]{11}" maxlength="11"
               data-placeholder-en="11-digit BVN"
               data-placeholder-ha="BVN lambobi 11"
               placeholder="11-digit BVN">
        <div class="invalid-feedback" id="bvn-error">BVN must be exactly 11 digits.</div>
        <div class="form-text" data-en="Mock field — stored but not verified against NIBSS or any bank." data-ha="Filin koyi — an adana amma ba a tabbatar da NIBSS ko wata banki.">Mock field — stored but not verified against NIBSS or any bank.</div>
      </div>

      <!-- Password -->
      <div class="mb-3">
        <label class="form-label" for="password" data-en="Password" data-ha="Kalmar Sirri">Password</label>
        <input type="password" class="form-control" id="password" name="password"
               required autocomplete="new-password"
               data-placeholder-en="At least 6 characters"
               data-placeholder-ha="Aƙalla haruffa 6"
               placeholder="At least 6 characters">
      </div>

      <!-- Confirm Password -->
      <div class="mb-3">
        <label class="form-label" for="password_confirm" data-en="Confirm Password" data-ha="Tabbatar da Kalmar Sirri">Confirm Password</label>
        <input type="password" class="form-control" id="password_confirm" name="password_confirm"
               required autocomplete="new-password"
               data-placeholder-en="Repeat password"
               data-placeholder-ha="Maimaita kalmar sirri"
               placeholder="Repeat password">
        <div class="invalid-feedback" id="pass-error">Passwords do not match.</div>
      </div>

      <div class="d-grid mt-4">
        <button type="submit" class="btn btn-primary" data-en="Create Account" data-ha="Ƙirƙiri Asusun">Create Account</button>
      </div>
    </form>

    <?php endif; ?>

    <hr style="border-color:var(--lm-border);margin:1.5rem 0">
    <p style="text-align:center;font-size:.875rem;color:var(--lm-ink-muted)">
      <span data-en="Already have an account?" data-ha="Kuna da asusun?">Already have an account?</span>
      <a href="/lamuni/public/login.php" data-en="Sign in" data-ha="Shiga">Sign in</a>
    </p>
    <p style="text-align:center;font-size:.8rem;margin-top:.5rem">
      <a href="/lamuni/public/admin/login.php" style="color:var(--lm-ink-muted)" data-en="Admin login →" data-ha="Shiga admin →">Admin login →</a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/lamuni/public/assets/js/app.js"></script>
</body>
</html>
