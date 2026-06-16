<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

final class App
{
    public static function registerAutoloader(string $srcDir): void
    {
        spl_autoload_register(static function (string $class) use ($srcDir): void {
            $prefix = 'App\\';
            if (!str_starts_with($class, $prefix)) {
                return;
            }
            $relative = substr($class, strlen($prefix));
            $file = $srcDir . '/' . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
            }
        });
    }

    public static function handleCors(string $origin): void
    {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Vary: Origin');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public static function registerErrorHandler(bool $debug): void
    {
        set_exception_handler(static function (Throwable $e) use ($debug): void {
            $payload = ['error' => 'Internal server error'];
            if ($debug) {
                $payload['message'] = $e->getMessage();
                $payload['trace']   = explode("\n", $e->getTraceAsString());
            }
            Response::json($payload, 500);
        });
    }
}
