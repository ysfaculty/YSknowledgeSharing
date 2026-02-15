<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function login(): void
    {
        $payload = requestJson();
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($email === '' || $password === '') {
            jsonResponse(['message' => 'email/password는 필수입니다.'], 422);
            return;
        }

        $result = $this->auth->login($email, $password);
        if (!$result) {
            jsonResponse(['message' => '로그인에 실패했습니다.'], 401);
            return;
        }

        jsonResponse($result);
    }

    public function me(): void
    {
        $token = getAuthorizationBearer();
        if (!$token) {
            jsonResponse(['message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->auth->currentUser($token);
        if (!$user) {
            jsonResponse(['message' => 'Unauthorized'], 401);
            return;
        }

        jsonResponse(['user' => $user]);
    }
}
