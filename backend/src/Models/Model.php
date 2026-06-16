<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class Model
{
    protected static function db(): PDO
    {
        return Database::pdo();
    }

    protected static function all(string $sql, array $params = []): array
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected static function one(string $sql, array $params = []): ?array
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    protected static function scalar(string $sql, array $params = [])
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    protected static function exec(string $sql, array $params = []): int
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    protected static function lastId(): int
    {
        return (int) self::db()->lastInsertId();
    }
}
