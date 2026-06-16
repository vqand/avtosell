<?php
declare(strict_types=1);

namespace App\Core;

final class JWT
{
    public static function encode(array $payload, string $secret, int $ttl): string
    {
        $now = time();
        $payload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $ttl,
        ]);

        $header  = self::b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body    = self::b64(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $sig     = self::sign("$header.$body", $secret);

        return "$header.$body.$sig";
    }

    public static function decode(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$header, $body, $sig] = $parts;

        $expected = self::sign("$header.$body", $secret);
        if (!hash_equals($expected, $sig)) {
            return null;
        }

        $payload = json_decode(self::b64decode($body), true);
        if (!is_array($payload)) {
            return null;
        }
        if (isset($payload['exp']) && time() >= (int) $payload['exp']) {
            return null;
        }
        return $payload;
    }

    private static function sign(string $data, string $secret): string
    {
        return self::b64(hash_hmac('sha256', $data, $secret, true));
    }

    private static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64decode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
