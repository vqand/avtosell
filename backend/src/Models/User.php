<?php
declare(strict_types=1);

namespace App\Models;

final class User extends Model
{
    private const PUBLIC_FIELDS =
        'u.id, u.name, u.email, u.phone, u.is_blocked, r.name AS role, u.created_at';

    public static function findByEmail(string $email): ?array
    {
        return self::one(
            'SELECT u.*, r.name AS role FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = ?',
            [$email]
        );
    }

    public static function findByLogin(string $login): ?array
    {

        return self::one(
            'SELECT u.*, r.name AS role FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? OR u.phone = ?',
            [$login, $login]
        );
    }

    public static function find(int $id): ?array
    {
        return self::one(
            'SELECT ' . self::PUBLIC_FIELDS . ', r.id AS role_id
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.id = ?',
            [$id]
        );
    }

    public static function create(string $name, string $email, ?string $phone, string $passwordHash, int $roleId = 2): int
    {
        self::exec(
            'INSERT INTO users (role_id, name, email, phone, password_hash)
             VALUES (?, ?, ?, ?, ?)',
            [$roleId, $name, $email, $phone, $passwordHash]
        );
        return self::lastId();
    }

    public static function updateProfile(int $id, string $name, ?string $phone): void
    {
        self::exec('UPDATE users SET name = ?, phone = ? WHERE id = ?', [$name, $phone, $id]);
    }

    public static function emailExists(string $email): bool
    {
        return (bool) self::scalar('SELECT 1 FROM users WHERE email = ?', [$email]);
    }

    public static function listAll(): array
    {
        return self::all(
            'SELECT ' . self::PUBLIC_FIELDS . ',
                (SELECT COUNT(*) FROM cars c WHERE c.user_id = u.id) AS cars_count
             FROM users u JOIN roles r ON r.id = u.role_id
             ORDER BY u.created_at DESC'
        );
    }

    public static function setBlocked(int $id, bool $blocked): void
    {
        self::exec('UPDATE users SET is_blocked = ? WHERE id = ?', [$blocked ? 1 : 0, $id]);
    }

    public static function delete(int $id): void
    {
        self::exec('DELETE FROM users WHERE id = ?', [$id]);
    }

    public static function adminUpdate(int $id, string $name, ?string $phone, int $roleId): void
    {
        self::exec('UPDATE users SET name = ?, phone = ?, role_id = ? WHERE id = ?', [$name, $phone, $roleId, $id]);
    }
}
