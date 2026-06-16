<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Car;
use App\Models\Favorite;

final class FavoriteController
{

    public function index(Request $req): void
    {
        Response::json(['data' => Favorite::listForUser((int) $req->userId())]);
    }

    public function add(Request $req, array $params): void
    {
        $carId = (int) $params['carId'];
        if (!Car::find($carId)) {
            Response::error('Listing not found', 404);
        }
        Favorite::add((int) $req->userId(), $carId);
        Response::json(['ok' => true, 'is_favorite' => true], 201);
    }

    public function remove(Request $req, array $params): void
    {
        Favorite::remove((int) $req->userId(), (int) $params['carId']);
        Response::json(['ok' => true, 'is_favorite' => false]);
    }
}
