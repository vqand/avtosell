<?php
declare(strict_types=1);

namespace App\Models;

final class Favorite extends Model
{
    public static function add(int $userId, int $carId): void
    {
        self::exec(
            'INSERT IGNORE INTO favorites (user_id, car_id) VALUES (?, ?)',
            [$userId, $carId]
        );
    }

    public static function remove(int $userId, int $carId): void
    {
        self::exec('DELETE FROM favorites WHERE user_id = ? AND car_id = ?', [$userId, $carId]);
    }

    public static function exists(int $userId, int $carId): bool
    {
        return (bool) self::scalar(
            'SELECT 1 FROM favorites WHERE user_id = ? AND car_id = ?',
            [$userId, $carId]
        );
    }

    public static function listForUser(int $userId): array
    {
        return self::all(
            'SELECT c.id, c.title, c.price, c.year, c.mileage, c.fuel_type,
                    c.transmission, c.drive_type, b.name AS brand,
                    (SELECT url FROM car_images ci WHERE ci.car_id = c.id
                       ORDER BY ci.is_primary DESC, ci.sort_order ASC LIMIT 1) AS image
             FROM favorites f
             JOIN cars c ON c.id = f.car_id
             JOIN brands b ON b.id = c.brand_id
             WHERE f.user_id = ?
             ORDER BY f.created_at DESC',
            [$userId]
        );
    }

    public static function idsForUser(int $userId): array
    {
        $rows = self::all('SELECT car_id FROM favorites WHERE user_id = ?', [$userId]);
        return array_map(static fn ($r) => (int) $r['car_id'], $rows);
    }
}
