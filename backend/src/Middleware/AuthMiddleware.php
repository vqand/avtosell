<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\JWT;
use App\Core\Request;
use App\Core\Response;

final class AuthMiddleware
{
    public function __construct(private string $secret) {}

    public function __invoke(Request $req): void
    {
        $token = $req->bearerToken();
        if ($token === null) {
            Response::error('Authentication required', 401);
        }
        $payload = JWT::decode($token, $this->secret);
        if ($payload === null) {
            Response::error('Invalid or expired token', 401);
        }
        $req->auth = $payload;
    }
}
