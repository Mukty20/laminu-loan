<?php
/**
 * Lamuni — Authentication
 * ----------------------------------------
 * Handles registration, login, logout, and route protection.
 * All passwords hashed with PASSWORD_BCRYPT (OWASP recommended).
 */

declare(strict_types=1);
require_once __DIR__ . '/Database.php';

class Auth
{
    /**
     * Register a new borrower account.
     *
     * @return true|string  true on success, error message string on failure
     */
    public static function register(
        string $name,
        string $email,
        string $phone,
        ?string $bvn,
        string $password
    ): true|string {
        // Server-side validation
        if (empty($name) || empty($email) || empty($password)) {
            return 'Name, email, and password are required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email address.';
        }
        if (!preg_match('/^\d{11}$/', $phone)) {
            return 'Phone number must be exactly 11 digits.';
        }
        if ($bvn !== null && $bvn !== '' && !preg_match('/^\d{11}$/', $bvn)) {
            return 'BVN must be exactly 11 digits.';
        }
        if (strlen($password) < 6) {
            return 'Password must be at least 6 characters.';
        }

        $pdo = Database::getConnection();

        // Check for duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            return 'An account with that email already exists.';
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, phone, bvn, password_hash, role)
             VALUES (:name, :email, :phone, :bvn, :password_hash, 'borrower')"
        );
        $stmt->execute([
            ':name'          => htmlspecialchars(strip_tags($name)),
            ':email'         => $email,
            ':phone'         => $phone,
            ':bvn'           => ($bvn !== '' ? $bvn : null),
            ':password_hash' => $hash,
        ]);

        $userId = (int)$pdo->lastInsertId();

        // Audit log
        require_once __DIR__ . '/AuditLog.php';
        AuditLog::log($userId, "Borrower registered: {$email}");

        return true;
    }

    /**
     * Attempt login. On success sets session and returns true.
     *
     * @param string $role  'borrower' | 'admin'
     * @return true|string  true or error message
     */
    public static function login(string $email, string $password, string $role): true|string
    {
        if (empty($email) || empty($password)) {
            return 'Email and password are required.';
        }

        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, name, email, password_hash, role, bvn FROM users WHERE email = :email AND role = :role"
        );
        $stmt->execute([':email' => $email, ':role' => $role]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return 'Invalid email or password.';
        }

        // Regenerate session id on privilege escalation
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_bvn']  = $user['bvn'];

        // Audit log
        require_once __DIR__ . '/AuditLog.php';
        AuditLog::log($user['id'], ucfirst($role) . " logged in: {$email}");

        return true;
    }

    /**
     * Destroy session and redirect to login page.
     */
    public static function logout(string $redirectTo = '/lamuni/public/'): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userId = $_SESSION['user_id'] ?? null;
        $email  = $_SESSION['user_email'] ?? 'unknown';

        if ($userId) {
            require_once __DIR__ . '/AuditLog.php';
            AuditLog::log($userId, "User logged out");
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: ' . $redirectTo);
        exit;
    }

    /** Ensure user is logged in; redirect to login if not. */
    public static function requireLogin(string $loginUrl = '/lamuni/public/login.php'): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    /** Ensure logged-in user is an admin; 403 otherwise. */
    public static function requireAdmin(): void
    {
        self::requireLogin('/lamuni/public/admin/login.php');
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            die('<h2>403 Forbidden</h2><p>Admin access required.</p>');
        }
    }

    /** Ensure logged-in user is a borrower. */
    public static function requireBorrower(): void
    {
        self::requireLogin('/lamuni/public/login.php');
        if (($_SESSION['user_role'] ?? '') !== 'borrower') {
            http_response_code(403);
            die('<h2>403 Forbidden</h2><p>Borrower access required.</p>');
        }
    }

    /** Return current user id or null. */
    public static function userId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /** Return current user role or null. */
    public static function role(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['user_role'] ?? null;
    }
}
