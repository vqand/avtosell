<?php
declare(strict_types=1);

namespace App\Models;

final class Report extends Model
{
    public static function create(int $carId, ?int $userId, string $reason): int
    {
        self::exec(
            'INSERT INTO reports (car_id, user_id, reason) VALUES (?, ?, ?)',
            [$carId, $userId, $reason]
        );
        return self::lastId();
    }

    public static function listAll(): array
    {
        return self::all(
            'SELECT r.*, c.title AS car_title, u.name AS reporter_name
             FROM reports r
             JOIN cars c ON c.id = r.car_id
             LEFT JOIN users u ON u.id = r.user_id
             ORDER BY FIELD(r.status, "open", "reviewed", "dismissed"), r.created_at DESC'
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        self::exec('UPDATE reports SET status = ? WHERE id = ?', [$status, $id]);
    }
}
