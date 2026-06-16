<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public string $method;
    public string $path;
    public array $query = [];
    public array $body = [];

    public array $auth = [];

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        $uri  = preg_replace('#^.*/index\.php#', '', $uri) ?? $uri;
        $this->path = '/' . trim($uri, '/');

        parse_str($_SERVER['QUERY_STRING'] ?? '', $this->query);

        $this->body = $this->parseBody();
    }

    private function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input') ?: '';
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        return $_POST ?: [];
    }

    public function input(string $key, $default = null)
    {
        return $this->body[$key] ?? $default;
    }

    public function queryParam(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $auth = $headers['Authorization']
            ?? $headers['authorization']
            ?? $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';
        if (preg_match('/Bearer\s+(.+)/i', (string) $auth, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    public function userId(): ?int
    {
        return isset($this->auth['sub']) ? (int) $this->auth['sub'] : null;
    }

    public function isAdmin(): bool
    {
        return ($this->auth['role'] ?? null) === 'admin';
    }
}
