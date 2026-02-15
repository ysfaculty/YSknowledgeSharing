<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Utils\Database;

class LikeRepository
{
    public function __construct(private readonly Database $db)
    {
    }

    public function hasUserLiked(int $postId, int $userId): bool
    {
        $row = $this->db->fetchOne(
            'SELECT id FROM post_likes WHERE post_id = :post_id AND user_id = :user_id',
            ['post_id' => $postId, 'user_id' => $userId]
        );

        return $row !== null;
    }

    public function countByPost(int $postId): int
    {
        $row = $this->db->fetchOne('SELECT COUNT(*) AS cnt FROM post_likes WHERE post_id = :post_id', ['post_id' => $postId]);
        return (int) ($row['cnt'] ?? 0);
    }

    public function like(int $postId, int $userId): void
    {
        if ($this->hasUserLiked($postId, $userId)) {
            return;
        }

        $nextIdRow = $this->db->fetchOne('SELECT post_likes_seq.NEXTVAL AS id FROM dual');
        $id = (int) ($nextIdRow['id'] ?? 0);
        $this->db->execute(
            'INSERT INTO post_likes (id, post_id, user_id, created_at)
             VALUES (:id, :post_id, :user_id, SYSTIMESTAMP)',
            ['id' => $id, 'post_id' => $postId, 'user_id' => $userId]
        );
    }

    public function unlike(int $postId, int $userId): void
    {
        $this->db->execute('DELETE FROM post_likes WHERE post_id = :post_id AND user_id = :user_id', ['post_id' => $postId, 'user_id' => $userId]);
    }
}
