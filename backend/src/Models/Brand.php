<?php
declare(strict_types=1);

namespace App\Models;

final class Brand extends Model
{
    public static function all(string $sql = '', array $params = []): array
    {
        return parent::all(
            'SELECT b.id, b.name, b.origin,
                    (SELECT COUNT(*) FROM cars c WHERE c.brand_id = b.id) AS cars_count
             FROM brands b ORDER BY b.name'
        );
    }

    public static function models(int $brandId): array
    {
        return parent::all(
            'SELECT id, name FROM models WHERE brand_id = ? ORDER BY name',
            [$brandId]
        );
    }

    public static function allModels(): array
    {
        return parent::all(
            'SELECT m.id, m.name, m.brand_id, b.name AS brand
             FROM models m JOIN brands b ON b.id = m.brand_id
             ORDER BY b.name, m.name'
        );
    }

    public static function create(string $name, string $origin): int
    {
        self::exec('INSERT INTO brands (name, origin) VALUES (?, ?)', [$name, $origin]);
        return self::lastId();
    }

    public static function update(int $id, string $name, string $origin): void
    {
        self::exec('UPDATE brands SET name = ?, origin = ? WHERE id = ?', [$name, $origin, $id]);
    }

    public static function delete(int $id): void
    {
        self::exec('DELETE FROM brands WHERE id = ?', [$id]);
    }

    public static function createModel(int $brandId, string $name): int
    {
        self::exec('INSERT INTO models (brand_id, name) VALUES (?, ?)', [$brandId, $name]);
        return self::lastId();
    }

    public static function exists(int $id): bool
    {
        return (bool) self::scalar('SELECT 1 FROM brands WHERE id = ?', [$id]);
    }

    public static function hasCars(int $id): bool
    {
        return (bool) self::scalar('SELECT 1 FROM cars WHERE brand_id = ? LIMIT 1', [$id]);
    }

    public static function modelBelongsTo(int $modelId, int $brandId): bool
    {
        return (bool) self::scalar(
            'SELECT 1 FROM models WHERE id = ? AND brand_id = ?',
            [$modelId, $brandId]
        );
    }
}
