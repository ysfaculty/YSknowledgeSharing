<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Utils\Database;

class CommentRepository
{
    public function __construct(private readonly Database $db)
    {
    }

    public function listByPost(int $postId): array
    {
        return $this->db->fetchAll(
            'SELECT c.id, c.content, c.created_at, u.display_name AS author_name
             FROM comments c
             JOIN users u ON u.id = c.user_id
             WHERE c.post_id = :post_id
             ORDER BY c.created_at DESC',
            ['post_id' => $postId]
        );
    }

    public function create(int $postId, int $userId, string $content): int
    {
        $nextIdRow = $this->db->fetchOne('SELECT comments_seq.NEXTVAL AS id FROM dual');
        $id = (int) ($nextIdRow['id'] ?? 0);
        $this->db->execute(
            'INSERT INTO comments (id, post_id, user_id, content, created_at)
             VALUES (:id, :post_id, :user_id, :content, SYSTIMESTAMP)',
            ['id' => $id, 'post_id' => $postId, 'user_id' => $userId, 'content' => $content]
        );

        return $id;
    }
}
