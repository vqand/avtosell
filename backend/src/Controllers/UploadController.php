<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Car;

final class UploadController
{
    public function __construct(private array $cfg, private string $publicDir) {}

    public function upload(Request $req, array $params): void
    {
        $carId = (int) $params['id'];
        $owner = Car::ownerId($carId);
        if ($owner === null) {
            Response::error('Listing not found', 404);
        }
        if ($owner !== (int) $req->userId() && !$req->isAdmin()) {
            Response::error('Forbidden', 403);
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            Response::error('No file uploaded (field "image")', 422);
        }

        $file = $_FILES['image'];
        if ($file['size'] > $this->cfg['max_bytes']) {
            Response::error('File too large', 422);
        }

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
        if (!isset($allowed[$mime])) {
            Response::error('Only JPEG, PNG, WebP allowed', 422);
        }

        $dir = $this->publicDir . '/' . trim($this->cfg['dir'], '/');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = sprintf('car%d_%s.%s', $carId, bin2hex(random_bytes(8)), $allowed[$mime]);
        $dest = "$dir/$name";

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Response::error('Failed to store file', 500);
        }

        $url = '/' . trim($this->cfg['dir'], '/') . '/' . $name;
        $isFirst = empty(Car::find($carId)['images']);
        $id = Car::addImage($carId, $url, $isFirst);

        Response::json(['id' => $id, 'url' => $url], 201);
    }
}
