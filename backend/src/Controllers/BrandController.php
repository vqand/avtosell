<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Brand;

final class BrandController
{

    public function index(Request $req): void
    {
        Response::json(['data' => Brand::all()]);
    }

    public function models(Request $req, array $params): void
    {
        Response::json(['data' => Brand::models((int) $params['id'])]);
    }

    public function allModels(Request $req): void
    {
        Response::json(['data' => Brand::allModels()]);
    }
}
