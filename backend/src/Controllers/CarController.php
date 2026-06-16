<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\JWT;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Favorite;
use App\Models\Report;

final class CarController
{
    public function __construct(private string $jwtSecret) {}

    public function index(Request $req): void
    {
        $result = Car::search($req->query, publicOnly: true);

        $favIds = [];
        if ($uid = $this->optionalUserId($req)) {
            $favIds = Favorite::idsForUser($uid);
        }
        foreach ($result['data'] as &$row) {
            $row['is_favorite'] = in_array((int) $row['id'], $favIds, true);
        }
        Response::json($result);
    }

    public function show(Request $req, array $params): void
    {
        $car = Car::find((int) $params['id']);
        if (!$car || $car['status'] !== 'approved' || (int) $car['is_active'] !== 1) {

            $uid = $this->optionalUserId($req);
            $isOwner = $car && $uid === (int) $car['user_id'];
            if (!$car || (!$isOwner && !$req->isAdmin())) {
                Response::error('Listing not found', 404);
            }
        }
        unset($car['seller_email']);
        if ($uid = $this->optionalUserId($req)) {
            $car['is_favorite'] = Favorite::exists($uid, (int) $car['id']);
        }
        Response::json(['car' => $car]);
    }

    public function store(Request $req): void
    {
        $this->validateCar($req);
        $id = Car::create((int) $req->userId(), $req->body);
        Response::json(['car' => Car::find($id)], 201);
    }

    public function update(Request $req, array $params): void
    {
        $id = (int) $params['id'];
        $this->authorizeOwner($req, $id);
        $this->validateCar($req);
        Car::update($id, $req->body);
        Response::json(['car' => Car::find($id)]);
    }

    public function destroy(Request $req, array $params): void
    {
        $id = (int) $params['id'];
        $this->authorizeOwner($req, $id);
        Car::delete($id);
        Response::json(['ok' => true]);
    }

    public function mine(Request $req): void
    {
        $result = Car::search(['user_id' => $req->userId()], publicOnly: false);
        Response::json($result);
    }

    public function report(Request $req, array $params): void
    {
        $id = (int) $params['id'];
        if (!Car::find($id)) {
            Response::error('Listing not found', 404);
        }
        $reason = trim((string) $req->input('reason'));
        if ($reason === '') {
            Response::error('Reason is required', 422);
        }
        Report::create($id, $this->optionalUserId($req), $reason);
        Response::json(['ok' => true], 201);
    }

    private function validateCar(Request $req): void
    {
        $v = new Validator($req->body, [
            'title'        => 'required|min:3|max:200',
            'brand_id'     => 'required|integer|min:1',
            'price'        => 'required|numeric|min:0',
            'year'         => 'required|integer|min:1950|max:2100',
            'mileage'      => 'integer|min:0',
            'fuel_type'    => 'required|in:petrol,diesel,hybrid,electric,gas',
            'transmission' => 'required|in:manual,automatic,robot,variator',
            'drive_type'   => 'required|in:rear,front,full',
        ]);
        if ($v->fails()) {
            Response::error('Validation failed', 422, ['fields' => $v->errors()]);
        }

        $brandId = (int) $req->input('brand_id');
        if (!Brand::exists($brandId)) {
            Response::error('Validation failed', 422, ['fields' => ['brand_id' => ['Выбран несуществующий бренд']]]);
        }

        $modelId = $req->input('model_id');
        if ($modelId !== null && $modelId !== '' && !Brand::modelBelongsTo((int) $modelId, $brandId)) {
            Response::error('Validation failed', 422, ['fields' => ['model_id' => ['Модель не относится к выбранному бренду']]]);
        }
    }

    private function authorizeOwner(Request $req, int $carId): void
    {
        $owner = Car::ownerId($carId);
        if ($owner === null) {
            Response::error('Listing not found', 404);
        }
        if ($owner !== (int) $req->userId() && !$req->isAdmin()) {
            Response::error('Forbidden', 403);
        }
    }

    private function optionalUserId(Request $req): ?int
    {
        if ($req->userId() !== null) {
            return $req->userId();
        }
        $token = $req->bearerToken();
        if ($token === null) {
            return null;
        }
        $payload = JWT::decode($token, $this->jwtSecret);
        if ($payload) {
            $req->auth = $payload;
            return (int) $payload['sub'];
        }
        return null;
    }
}
