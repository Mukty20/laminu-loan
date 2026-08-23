<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/Auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    header('Location: /lamuni/public/admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    $result = Auth::login($email, $password, 'admin');
    if ($result === true) {
        header('Location: /lamuni/public/admin/dashboard.php');
        exit;
    } else {
        $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login — Lamuni</title>
  <meta name="description" content="Lamuni administrator sign-in page.">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lamuni/public/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="mb-3">
      <div class="auth-logo">Lamu<span>ni</span></div>
      <div class="auth-subtitle">Administrator Sign In</div>
    </div>

    <div style="display:inline-block;background:var(--lm-accent-light);border:1px solid var(--lm-accent);border-radius:var(--lm-radius);padding:.3rem .75rem;font-size:.78rem;font-weight:600;color:var(--lm-accent);margin-bottom:1.25rem">
      ADMIN PORTAL
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="mb-3">
        <label class="form-label" for="admin-email">Email Address</label>
        <input type="email" class="form-control" id="admin-email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               required autocomplete="email" placeholder="admin@lamuni.local">
      </div>
      <div class="mb-4">
        <label class="form-label" for="admin-password">Password</label>
        <input type="password" class="form-control" id="admin-password" name="password"
               required autocomplete="current-password" placeholder="••••••••">
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary" id="admin-login-btn">Sign In</button>
      </div>
    </form>

    <div style="margin-top:1rem;padding:.75rem;background:var(--lm-bg);border:1px solid var(--lm-border);border-radius:var(--lm-radius);font-size:.8rem;color:var(--lm-ink-muted)">
      <strong>Demo credentials:</strong><br>
      admin@lamuni.local / admin123
    </div>

    <hr style="border-color:var(--lm-border);margin:1.5rem 0">
    <p style="text-align:center;font-size:.875rem;color:var(--lm-ink-muted)">
      <a href="/lamuni/public/login.php" style="color:var(--lm-ink-muted)">← Borrower login</a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/lamuni/public/assets/js/app.js"></script>
</body>
</html>
