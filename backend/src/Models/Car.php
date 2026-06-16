<?php
declare(strict_types=1);

namespace App\Models;

final class Car extends Model
{

    public static function search(array $f, bool $publicOnly = true): array
    {
        $where  = [];
        $params = [];

        if ($publicOnly) {
            $where[] = "c.status = 'approved' AND c.is_active = 1";
        } elseif (!empty($f['status'])) {
            $where[] = 'c.status = ?';
            $params[] = $f['status'];
        }

        if (!empty($f['user_id'])) {
            $where[] = 'c.user_id = ?';
            $params[] = (int) $f['user_id'];
        }

        if (!empty($f['q'])) {
            $boolean = self::booleanQuery((string) $f['q']);
            if ($boolean !== '') {
                $where[]  = 'MATCH(c.title, c.description) AGAINST (? IN BOOLEAN MODE)';
                $params[] = $boolean;
            }
        }

        $brandIds = self::idList($f['brand_ids'] ?? ($f['brand_id'] ?? null));
        if ($brandIds) {
            $where[] = 'c.brand_id IN (' . implode(',', array_fill(0, count($brandIds), '?')) . ')';
            array_push($params, ...$brandIds);
        }

        if (!empty($f['model_id'])) {
            $where[] = 'c.model_id = ?';
            $params[] = (int) $f['model_id'];
        }

        if (!empty($f['origin']) && in_array($f['origin'], ['domestic', 'foreign'], true)) {
            $where[] = 'b.origin = ?';
            $params[] = $f['origin'];
        }

        foreach ([
            'price_min'   => ['c.price', '>='],
            'price_max'   => ['c.price', '<='],
            'year_min'    => ['c.year', '>='],
            'year_max'    => ['c.year', '<='],
            'mileage_max' => ['c.mileage', '<='],
        ] as $key => [$col, $op]) {
            if (isset($f[$key]) && $f[$key] !== '') {
                $where[]  = "$col $op ?";
                $params[] = $f[$key];
            }
        }

        foreach (['fuel_type', 'transmission', 'drive_type'] as $enum) {
            if (!empty($f[$enum])) {
                $where[]  = "c.$enum = ?";
                $params[] = $f[$enum];
            }
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sort = match ($f['sort'] ?? 'new') {
            'price_asc'  => 'c.price ASC',
            'price_desc' => 'c.price DESC',
            'year_desc'  => 'c.year DESC',
            default      => 'c.created_at DESC',
        };

        $page    = max(1, (int) ($f['page'] ?? 1));
        $perPage = min(60, max(1, (int) ($f['per_page'] ?? 12)));
        $offset  = ($page - 1) * $perPage;

        $total = (int) self::scalar(
            "SELECT COUNT(*) FROM cars c JOIN brands b ON b.id = c.brand_id $whereSql",
            $params
        );

        $rows = self::all(
            "SELECT c.id, c.title, c.price, c.year, c.mileage, c.fuel_type,
                    c.transmission, c.drive_type, c.status, c.is_active, c.created_at,
                    b.name AS brand, b.origin,
                    (SELECT url FROM car_images ci WHERE ci.car_id = c.id
                       ORDER BY ci.is_primary DESC, ci.sort_order ASC LIMIT 1) AS image
             FROM cars c
             JOIN brands b ON b.id = c.brand_id
             $whereSql
             ORDER BY $sort
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return [
            'data' => $rows,
            'meta' => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
                'pages'    => (int) ceil($total / $perPage),
            ],
        ];
    }

    public static function find(int $id): ?array
    {
        $car = self::one(
            'SELECT c.*, b.name AS brand, b.origin, m.name AS model,
                    u.name AS seller_name, u.phone AS seller_phone, u.email AS seller_email
             FROM cars c
             JOIN brands b ON b.id = c.brand_id
             LEFT JOIN models m ON m.id = c.model_id
             JOIN users u ON u.id = c.user_id
             WHERE c.id = ?',
            [$id]
        );
        if ($car === null) {
            return null;
        }
        $car['images'] = self::all(
            'SELECT id, url, is_primary, sort_order FROM car_images
             WHERE car_id = ? ORDER BY is_primary DESC, sort_order ASC',
            [$id]
        );
        return $car;
    }

    public static function create(int $userId, array $d): int
    {
        self::exec(
            'INSERT INTO cars
              (user_id, brand_id, model_id, title, description, price, year, mileage,
               fuel_type, transmission, drive_type, body_type, engine_volume, power_hp,
               color, owners_count, vin, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, "approved")',
            [
                $userId, $d['brand_id'], $d['model_id'] ?? null, $d['title'],
                $d['description'] ?? null, $d['price'], $d['year'], $d['mileage'] ?? 0,
                $d['fuel_type'], $d['transmission'], $d['drive_type'],
                $d['body_type'] ?? null, $d['engine_volume'] ?? null, $d['power_hp'] ?? null,
                $d['color'] ?? null, $d['owners_count'] ?? null, $d['vin'] ?? null,
            ]
        );
        return self::lastId();
    }

    public static function update(int $id, array $d): void
    {
        self::exec(
            'UPDATE cars SET
                brand_id=?, model_id=?, title=?, description=?, price=?, year=?, mileage=?,
                fuel_type=?, transmission=?, drive_type=?, body_type=?, engine_volume=?,
                power_hp=?, color=?, owners_count=?, vin=?
             WHERE id=?',
            [
                $d['brand_id'], $d['model_id'] ?? null, $d['title'], $d['description'] ?? null,
                $d['price'], $d['year'], $d['mileage'] ?? 0, $d['fuel_type'],
                $d['transmission'], $d['drive_type'], $d['body_type'] ?? null,
                $d['engine_volume'] ?? null, $d['power_hp'] ?? null, $d['color'] ?? null,
                $d['owners_count'] ?? null, $d['vin'] ?? null, $id,
            ]
        );
    }

    public static function delete(int $id): void
    {
        self::exec('DELETE FROM cars WHERE id = ?', [$id]);
    }

    public static function ownerId(int $id): ?int
    {
        $v = self::scalar('SELECT user_id FROM cars WHERE id = ?', [$id]);
        return $v === false ? null : (int) $v;
    }

    public static function setStatus(int $id, string $status): void
    {
        self::exec('UPDATE cars SET status = ? WHERE id = ?', [$status, $id]);
    }

    public static function addImage(int $carId, string $url, bool $primary = false): int
    {
        self::exec(
            'INSERT INTO car_images (car_id, url, is_primary) VALUES (?, ?, ?)',
            [$carId, $url, $primary ? 1 : 0]
        );
        return self::lastId();
    }

    private static function idList($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        $items = is_array($value) ? $value : explode(',', (string) $value);
        return array_values(array_filter(array_map('intval', $items)));
    }

    private static function booleanQuery(string $q): string
    {
        $q = preg_replace('/[+\-><()~*"@]+/u', ' ', $q) ?? '';
        $terms = preg_split('/\s+/u', trim($q), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $parts = array_map(static fn ($t) => '+' . $t . '*', $terms);
        return implode(' ', $parts);
    }
}
