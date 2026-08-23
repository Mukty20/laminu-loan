<?php
/**
 * Lamuni — Audit Log
 */

declare(strict_types=1);
require_once __DIR__ . '/Database.php';

class AuditLog
{
    /** Append an action to the audit log. */
    public static function log(?int $userId, string $action): void
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO audit_log (user_id, action) VALUES (:user_id, :action)"
        );
        $stmt->execute([':user_id' => $userId, ':action' => $action]);
    }

    /** Retrieve all audit log entries (newest first). Admin use only. */
    public static function getAll(int $limit = 200): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT al.*, u.name AS user_name, u.email AS user_email
               FROM audit_log al
          LEFT JOIN users u ON u.id = al.user_id
           ORDER BY al.created_at DESC
              LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
