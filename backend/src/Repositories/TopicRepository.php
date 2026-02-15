<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Utils\Database;

class TopicRepository
{
    public function __construct(private readonly Database $db)
    {
    }

    public function listAll(): array
    {
        return $this->db->fetchAll('SELECT id, code, name, description FROM topics WHERE is_active = 1 ORDER BY sort_order ASC');
    }

    public function findByCode(string $code): ?array
    {
        return $this->db->fetchOne('SELECT id, code, name, description FROM topics WHERE code = :code AND is_active = 1', ['code' => $code]);
    }
}
