<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

final class AdminMiddleware
{
    public function __invoke(Request $req): void
    {
        if (($req->auth['role'] ?? null) !== 'admin') {
            Response::error('Administrator access required', 403);
        }
    }
}
