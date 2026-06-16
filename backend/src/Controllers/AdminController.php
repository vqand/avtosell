<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Report;
use App\Models\Stats;
use App\Models\User;

final class AdminController
{

    public function dashboard(Request $req): void
    {
        Response::json([
            'stats'       => Stats::overview(),
            'recent_cars' => Stats::recentCars(8),
            'recent_logs' => Stats::recentLogs(10),
        ]);
    }

    public function users(Request $req): void
    {
        Response::json(['data' => User::listAll()]);
    }

    public function updateUser(Request $req, array $params): void
    {
        $id = (int) $params['id'];
        $v = new Validator($req->body, [
            'name'    => 'required|min:2|max:120',
            'role_id' => 'required|in:1,2',
        ]);
        if ($v->fails()) {
            Response::error('Validation failed', 422, ['fields' => $v->errors()]);
        }
        User::adminUpdate($id, (string) $req->input('name'), $req->input('phone'), (int) $req->input('role_id'));
        Stats::logAction((int) $req->userId(), 'update_user', 'user', $id);
        Response::json(['user' => User::find($id)]);
    }

    public function blockUser(Request $req, array $params): void
    {
        $id = (int) $params['id'];
        $blocked = (bool) $req->input('blocked', true);
        if ($id === (int) $req->userId()) {
            Response::error('You cannot block yourself', 400);
        }
        User::setBlocked($id, $blocked);
        Stats::logAction((int) $req->userId(), $blocked ? 'block_user' : 'unblock_user', 'user', $id);
        Response::json(['ok' => true]);
    }

    public function deleteUser(Request $req, array $params): void
    {
        $id = (int) $params['id'];
        if ($id === (int) $req->userId()) {
            Response::error('You cannot delete yourself', 400);
        }
        User::delete($id);
        Stats::logAction((int) $req->userId(), 'delete_user', 'user', $id);
        Response::json(['ok' => true]);
    }

    public function cars(Request $req): void
    {
        Response::json(Car::search($req->query, publicOnly: false));
    }

    public function setCarStatus(Request $req, array $params): void
    {
        $id = (int) $params['id'];
        $status = (string) $req->input('status');
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            Response::error('Invalid status', 422);
        }
        if (!Car::find($id)) {
            Response::error('Listing not found', 404);
        }
        Car::setStatus($id, $status);
        Stats::logAction((int) $req->userId(), "car_$status", 'car', $id);
        Response::json(['car' => Car::find($id)]);
    }

    public function deleteCar(Request $req, array $params): void
    {
        $id = (int) $params['id'];
        Car::delete($id);
        Stats::logAction((int) $req->userId(), 'delete_car', 'car', $id);
        Response::json(['ok' => true]);
    }

    public function reports(Request $req): void
    {
        Response::json(['data' => Report::listAll()]);
    }

    public function setReportStatus(Request $req, array $params): void
    {
        $id = (int) $params['id'];
        $status = (string) $req->input('status');
        if (!in_array($status, ['open', 'reviewed', 'dismissed'], true)) {
            Response::error('Invalid status', 422);
        }
        Report::setStatus($id, $status);
        Stats::logAction((int) $req->userId(), "report_$status", 'report', $id);
        Response::json(['ok' => true]);
    }

    public function createBrand(Request $req): void
    {
        $v = new Validator($req->body, [
            'name'   => 'required|min:1|max:80',
            'origin' => 'required|in:domestic,foreign',
        ]);
        if ($v->fails()) {
            Response::error('Validation failed', 422, ['fields' => $v->errors()]);
        }
        $id = Brand::create((string) $req->input('name'), (string) $req->input('origin'));
        Stats::logAction((int) $req->userId(), 'create_brand', 'brand', $id);
        Response::json(['id' => $id], 201);
    }

    public function updateBrand(Request $req, array $params): void
    {
        $id = (int) $params['id'];
        Brand::update($id, (string) $req->input('name'), (string) $req->input('origin'));
        Stats::logAction((int) $req->userId(), 'update_brand', 'brand', $id);
        Response::json(['ok' => true]);
    }

    public function deleteBrand(Request $req, array $params): void
    {
        $id = (int) $params['id'];
        if (!Brand::exists($id)) {
            Response::error('Бренд не найден', 404);
        }
        if (Brand::hasCars($id)) {
            Response::error('Нельзя удалить бренд: есть связанные объявления', 409);
        }
        Brand::delete($id);
        Stats::logAction((int) $req->userId(), 'delete_brand', 'brand', $id);
        Response::json(['ok' => true]);
    }

    public function createModel(Request $req): void
    {
        $v = new Validator($req->body, [
            'brand_id' => 'required|integer',
            'name'     => 'required|min:1|max:120',
        ]);
        if ($v->fails()) {
            Response::error('Validation failed', 422, ['fields' => $v->errors()]);
        }
        $id = Brand::createModel((int) $req->input('brand_id'), (string) $req->input('name'));
        Response::json(['id' => $id], 201);
    }
}
