<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Utils\Database;

class UserRepository
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            'SELECT id, email, password_hash, display_name FROM users WHERE email = :email',
            ['email' => $email]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT id, email, display_name FROM users WHERE id = :id',
            ['id' => $id]
        );
    }
}
