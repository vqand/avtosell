<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\JWT;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\User;

final class AuthController
{
    public function __construct(private array $jwtCfg) {}

    public function register(Request $req): void
    {
        $v = new Validator($req->body, [
            'name'     => 'required|min:2|max:120',
            'email'    => 'required|email|max:190',
            'password' => 'required|min:6|max:72',
        ]);
        if ($v->fails()) {
            Response::error('Validation failed', 422, ['fields' => $v->errors()]);
        }

        $email = strtolower(trim((string) $req->input('email')));
        if (User::emailExists($email)) {
            Response::error('Email already registered', 409);
        }

        $hash = password_hash((string) $req->input('password'), PASSWORD_BCRYPT);
        $id = User::create(
            trim((string) $req->input('name')),
            $email,
            self::sanitizePhone($req->input('phone')),
            $hash
        );

        $user = User::find($id);
        Response::json($this->withToken($user), 201);
    }

    public function login(Request $req): void
    {

        $login = trim((string) ($req->input('login') ?? $req->input('email') ?? ''));
        $password = (string) $req->input('password');

        if ($login === '' || $password === '') {
            Response::error('Login and password are required', 422);
        }

        $user = User::findByLogin($login);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            Response::error('Invalid credentials', 401);
        }
        if ((int) $user['is_blocked'] === 1) {
            Response::error('Account is blocked', 403);
        }

        Response::json($this->withToken(User::find((int) $user['id'])));
    }

    public function me(Request $req): void
    {
        $user = User::find((int) $req->userId());
        if (!$user) {
            Response::error('User not found', 404);
        }
        Response::json(['user' => $user]);
    }

    public function updateProfile(Request $req): void
    {
        $v = new Validator($req->body, ['name' => 'required|min:2|max:120']);
        if ($v->fails()) {
            Response::error('Validation failed', 422, ['fields' => $v->errors()]);
        }
        User::updateProfile(
            (int) $req->userId(),
            trim((string) $req->input('name')),
            self::sanitizePhone($req->input('phone'))
        );
        Response::json(['user' => User::find((int) $req->userId())]);
    }

    private function withToken(array $user): array
    {
        $token = JWT::encode(
            ['sub' => (int) $user['id'], 'role' => $user['role'], 'name' => $user['name']],
            $this->jwtCfg['secret'],
            $this->jwtCfg['ttl']
        );
        return ['token' => $token, 'user' => $user];
    }

    private static function sanitizePhone($phone): ?string
    {
        $phone = trim((string) $phone);
        return $phone === '' ? null : $phone;
    }
}
