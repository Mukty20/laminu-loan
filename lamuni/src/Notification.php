<?php
/**
 * Lamuni — Notification Model
 */

declare(strict_types=1);
require_once __DIR__ . '/Database.php';

class Notification
{
    /** Create a notification for a user. */
    public static function create(int $userId, string $message): void
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)"
        );
        $stmt->execute([':user_id' => $userId, ':message' => $message]);
    }

    /** Get all notifications for a user (newest first). */
    public static function getForUser(int $userId, int $limit = 20): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :lim"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim',     $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Count unread notifications for a user. */
    public static function countUnread(int $userId): int
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0"
        );
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Mark all notifications as read for a user. */
    public static function markAllRead(int $userId): void
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id"
        );
        $stmt->execute([':user_id' => $userId]);
    }
}
