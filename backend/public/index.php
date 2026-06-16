<?php
declare(strict_types=1);

use App\Core\App;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\BrandController;
use App\Controllers\CarController;
use App\Controllers\FavoriteController;
use App\Controllers\UploadController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

$root = dirname(__DIR__);

require $root . '/src/Core/App.php';
App::registerAutoloader($root . '/src');

$config = require $root . '/config/config.php';

App::registerErrorHandler((bool) $config['app']['debug']);
App::handleCors($config['cors']['origin']);
Database::init($config['db']);

$req = new Request();
$router = new Router();

$auth  = new AuthMiddleware($config['jwt']['secret']);
$admin = new AdminMiddleware();

$authCtrl   = new AuthController($config['jwt']);
$carCtrl    = new CarController($config['jwt']['secret']);
$favCtrl    = new FavoriteController();
$brandCtrl  = new BrandController();
$adminCtrl  = new AdminController();
$uploadCtrl = new UploadController($config['upload'], $root . '/public');

$router->get('/api/health', fn () => Response::json(['status' => 'ok']));

$router->post('/api/auth/register', [$authCtrl, 'register']);
$router->post('/api/auth/login',    [$authCtrl, 'login']);
$router->get('/api/auth/me',        [$authCtrl, 'me'],            [$auth]);
$router->put('/api/auth/profile',   [$authCtrl, 'updateProfile'], [$auth]);

$router->get('/api/cars',            [$carCtrl, 'index']);
$router->get('/api/cars/{id}',       [$carCtrl, 'show']);
$router->post('/api/cars/{id}/report', [$carCtrl, 'report']);

$router->get('/api/brands',              [$brandCtrl, 'index']);
$router->get('/api/brands/{id}/models',  [$brandCtrl, 'models']);
$router->get('/api/models',              [$brandCtrl, 'allModels']);

$router->get('/api/my/cars',         [$carCtrl, 'mine'],    [$auth]);
$router->post('/api/cars',           [$carCtrl, 'store'],   [$auth]);
$router->put('/api/cars/{id}',       [$carCtrl, 'update'],  [$auth]);
$router->delete('/api/cars/{id}',    [$carCtrl, 'destroy'], [$auth]);
$router->post('/api/cars/{id}/images', [$uploadCtrl, 'upload'], [$auth]);

$router->get('/api/favorites',            [$favCtrl, 'index'],  [$auth]);
$router->post('/api/favorites/{carId}',   [$favCtrl, 'add'],    [$auth]);
$router->delete('/api/favorites/{carId}', [$favCtrl, 'remove'], [$auth]);

$adminMw = [$auth, $admin];

$router->get('/api/admin/dashboard',        [$adminCtrl, 'dashboard'], $adminMw);

$router->get('/api/admin/users',            [$adminCtrl, 'users'],      $adminMw);
$router->put('/api/admin/users/{id}',       [$adminCtrl, 'updateUser'], $adminMw);
$router->patch('/api/admin/users/{id}/block', [$adminCtrl, 'blockUser'], $adminMw);
$router->delete('/api/admin/users/{id}',    [$adminCtrl, 'deleteUser'], $adminMw);

$router->get('/api/admin/cars',             [$adminCtrl, 'cars'],         $adminMw);
$router->patch('/api/admin/cars/{id}/status', [$adminCtrl, 'setCarStatus'], $adminMw);
$router->delete('/api/admin/cars/{id}',     [$adminCtrl, 'deleteCar'],    $adminMw);

$router->get('/api/admin/reports',          [$adminCtrl, 'reports'],         $adminMw);
$router->patch('/api/admin/reports/{id}',   [$adminCtrl, 'setReportStatus'], $adminMw);

$router->post('/api/admin/brands',          [$adminCtrl, 'createBrand'], $adminMw);
$router->put('/api/admin/brands/{id}',      [$adminCtrl, 'updateBrand'], $adminMw);
$router->delete('/api/admin/brands/{id}',   [$adminCtrl, 'deleteBrand'], $adminMw);
$router->post('/api/admin/models',          [$adminCtrl, 'createModel'], $adminMw);

$router->dispatch($req);
