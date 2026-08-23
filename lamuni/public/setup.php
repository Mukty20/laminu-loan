<?php
/**
 * Lamuni — One-time Setup / Seeder
 * ----------------------------------------
 * Run this ONCE after importing schema.sql and seed.sql:
 *   http://localhost/lamuni/public/setup.php
 *
 * This script inserts demo user accounts with properly hashed passwords.
 * DELETE this file (or restrict access) after running it.
 */

declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';

$pdo = Database::getConnection();

// Check if demo users already exist
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$count = (int)$stmt->fetchColumn();

if ($count > 0) {
    echo '<p style="color:darkred;font-family:monospace">Setup already completed (users table is not empty). Delete this file for security.</p>';
    exit;
}

$users = [
    [
        'id'    => 1,
        'name'  => 'Demo Borrower',
        'email' => 'demo@lamuni.local',
        'phone' => '07012345678',
        'bvn'   => '22112233445',
        'pass'  => 'demo1234',
        'role'  => 'borrower',
    ],
    [
        'id'    => 2,
        'name'  => 'Admin User',
        'email' => 'admin@lamuni.local',
        'phone' => '08000000000',
        'bvn'   => null,
        'pass'  => 'admin123',
        'role'  => 'admin',
    ],
];

$sql = "INSERT INTO users (id, name, email, phone, bvn, password_hash, role)
        VALUES (:id, :name, :email, :phone, :bvn, :password_hash, :role)";
$stmt = $pdo->prepare($sql);

foreach ($users as $u) {
    $stmt->execute([
        ':id'            => $u['id'],
        ':name'          => $u['name'],
        ':email'         => $u['email'],
        ':phone'         => $u['phone'],
        ':bvn'           => $u['bvn'],
        ':password_hash' => password_hash($u['pass'], PASSWORD_BCRYPT),
        ':role'          => $u['role'],
    ]);
}

echo '<!DOCTYPE html><html><body style="font-family:monospace;padding:2rem">';
echo '<h2>✅ Lamuni Setup Complete</h2>';
echo '<p>Demo accounts created:</p><ul>';
echo '<li><strong>Borrower</strong>: demo@lamuni.local / demo1234</li>';
echo '<li><strong>Admin</strong>: admin@lamuni.local / admin123</li>';
echo '</ul>';
echo '<p style="color:darkred"><strong>⚠ Delete this file (setup.php) now for security!</strong></p>';
echo '<p><a href="/lamuni/public/">Go to Lamuni</a></p>';
echo '</body></html>';
