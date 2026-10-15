<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/Auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['user_id']) && $_SESSION['user_role'] === 'borrower') {
    header('Location: /lamuni/public/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrfToken($_POST['_csrf'] ?? null)) {
        $error = 'Your session expired. Please reload the page and try again.';
    } else {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        $result = Auth::login($email, $password, 'borrower');
        if ($result === true) {
            header('Location: /lamuni/public/dashboard.php');
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
  <title>Borrower Login — Lamuni</title>
  <meta name="description" content="Sign in to your Lamuni borrower account.">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lamuni/public/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <div class="auth-logo">Lamu<span>ni</span></div>
        <div class="auth-subtitle" data-en="Borrower Sign In" data-ha="Shigan ɗan aro">Borrower Sign In</div>
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

    <form method="post" action="">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Auth::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
      <div class="mb-3">
        <label class="form-label" for="login-email" data-en="Email Address" data-ha="Adireshin Imel">Email Address</label>
        <input type="email" class="form-control" id="login-email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               required autocomplete="email" placeholder="demo@lamuni.local">
      </div>
      <div class="mb-4">
        <label class="form-label" for="login-password" data-en="Password" data-ha="Kalmar Sirri">Password</label>
        <input type="password" class="form-control" id="login-password" name="password"
               required autocomplete="current-password" placeholder="••••••••">
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary" data-en="Sign In" data-ha="Shiga">Sign In</button>
      </div>
    </form>

    <div style="margin-top:1rem;padding:.75rem;background:var(--lm-bg);border:1px solid var(--lm-border);border-radius:var(--lm-radius);font-size:.8rem;color:var(--lm-ink-muted)">
      <strong data-en="Demo credentials:" data-ha="Bayanan gwaji:">Demo credentials:</strong><br>
      demo@lamuni.local / demo1234
    </div>

    <hr style="border-color:var(--lm-border);margin:1.5rem 0">
    <p style="text-align:center;font-size:.875rem;color:var(--lm-ink-muted)">
      <span data-en="No account?" data-ha="Ba ku da asusun?">No account?</span>
      <a href="/lamuni/public/register.php" data-en="Create one" data-ha="Ƙirƙira">Create one</a>
    </p>
    <p style="text-align:center;font-size:.8rem;margin-top:.5rem">
      <a href="/lamuni/public/admin/login.php" style="color:var(--lm-ink-muted)" data-en="Admin login →" data-ha="Shiga admin →">Admin login →</a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/lamuni/public/assets/js/app.js?v=2"></script>
</body>
</html>
