<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{

    private array $routes = [];

    public function add(string $method, string $path, callable $handler, array $middleware = []): void
    {

        $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [
            'method'     => strtoupper($method),
            'pattern'    => '#^' . $pattern . '$#',
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function get(string $p, callable $h, array $m = []): void    { $this->add('GET', $p, $h, $m); }
    public function post(string $p, callable $h, array $m = []): void   { $this->add('POST', $p, $h, $m); }
    public function put(string $p, callable $h, array $m = []): void    { $this->add('PUT', $p, $h, $m); }
    public function patch(string $p, callable $h, array $m = []): void  { $this->add('PATCH', $p, $h, $m); }
    public function delete(string $p, callable $h, array $m = []): void { $this->add('DELETE', $p, $h, $m); }

    public function dispatch(Request $req): void
    {
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $req->path, $matches)) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $req->method) {
                continue;
            }

            foreach ($route['middleware'] as $mw) {
                $mw($req);
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            ($route['handler'])($req, $params);
            return;
        }

        if ($pathMatched) {
            Response::error('Method not allowed', 405);
        }
        Response::error('Not found', 404);
    }
}
