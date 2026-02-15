<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Utils\Jwt;

class AuthService
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function login(string $email, string $password): ?array
    {
        $user = $this->users->findByEmail($email);
        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        $ttl = (int) env('JWT_TTL', '3600');
        $payload = [
            'sub' => (int) $user['id'],
            'email' => $user['email'],
            'display_name' => $user['display_name'],
            'iat' => time(),
            'exp' => time() + $ttl,
        ];

        $token = Jwt::encode($payload, env('APP_SECRET', 'secret'));

        return ['token' => $token, 'user' => ['id' => (int) $user['id'], 'email' => $user['email'], 'display_name' => $user['display_name']]];
    }

    public function currentUser(string $token): ?array
    {
        $payload = Jwt::decode($token, env('APP_SECRET', 'secret'));
        if (!$payload || !isset($payload['sub'])) {
            return null;
        }

        return $this->users->findById((int) $payload['sub']);
    }
}
