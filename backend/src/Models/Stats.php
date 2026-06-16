<?php
declare(strict_types=1);

namespace App\Models;

final class Stats extends Model
{
    public static function overview(): array
    {
        return [
            'total_users'    => (int) self::scalar('SELECT COUNT(*) FROM users'),
            'total_cars'     => (int) self::scalar('SELECT COUNT(*) FROM cars'),
            'active_cars'    => (int) self::scalar("SELECT COUNT(*) FROM cars WHERE status = 'approved' AND is_active = 1"),
            'pending_cars'   => (int) self::scalar("SELECT COUNT(*) FROM cars WHERE status = 'pending'"),
            'open_reports'   => (int) self::scalar("SELECT COUNT(*) FROM reports WHERE status = 'open'"),
            'blocked_users'  => (int) self::scalar('SELECT COUNT(*) FROM users WHERE is_blocked = 1'),
        ];
    }

    public static function recentCars(int $limit = 5): array
    {
        return self::all(
            'SELECT c.id, c.title, c.price, c.status, c.created_at, u.name AS seller
             FROM cars c JOIN users u ON u.id = c.user_id
             ORDER BY c.created_at DESC LIMIT ' . (int) $limit
        );
    }

    public static function logAction(int $adminId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
    {
        self::exec(
            'INSERT INTO admin_logs (admin_id, action, entity_type, entity_id, details)
             VALUES (?, ?, ?, ?, ?)',
            [$adminId, $action, $entityType, $entityId, $details]
        );
    }

    public static function recentLogs(int $limit = 20): array
    {
        return self::all(
            'SELECT l.*, u.name AS admin_name
             FROM admin_logs l JOIN users u ON u.id = l.admin_id
             ORDER BY l.created_at DESC LIMIT ' . (int) $limit
        );
    }
}
