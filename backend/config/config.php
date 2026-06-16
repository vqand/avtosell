<?php
declare(strict_types=1);

function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value);
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

function env(string $key, $default = null)
{
    $val = getenv($key);
    if ($val === false) {
        return $default;
    }
    return match (strtolower((string) $val)) {
        'true'  => true,
        'false' => false,
        'null'  => null,
        default => $val,
    };
}

load_env(dirname(__DIR__) . '/.env');

return [
    'app' => [
        'env'   => env('APP_ENV', 'development'),
        'debug' => (bool) env('APP_DEBUG', true),
    ],
    'cors' => [
        'origin' => env('CORS_ORIGIN', 'http://localhost:5173'),
    ],
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => (int) env('DB_PORT', 3306),
        'name' => env('DB_NAME', 'fvito'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
    ],
    'jwt' => [
        'secret' => env('JWT_SECRET', 'insecure-dev-secret-change-me'),
        'ttl'    => (int) env('JWT_TTL', 86400),
    ],
    'upload' => [
        'dir'       => env('UPLOAD_DIR', 'uploads'),
        'max_bytes' => (int) env('UPLOAD_MAX_BYTES', 5242880),
    ],
];
