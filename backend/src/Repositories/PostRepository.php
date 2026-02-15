<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Utils\Database;

class PostRepository
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne('SELECT id, topic_id, user_id, title, content, created_at FROM posts WHERE id = :id', ['id' => $id]);
    }

    public function listByTopic(int $topicId): array
    {
        return $this->db->fetchAll(
            'SELECT p.id, p.title, p.content, p.created_at, u.display_name AS author_name,
                    (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id) AS like_count
             FROM posts p
             JOIN users u ON u.id = p.user_id
             WHERE p.topic_id = :topic_id
             ORDER BY p.created_at DESC',
            ['topic_id' => $topicId]
        );
    }

    public function create(int $topicId, int $userId, string $title, string $content): int
    {
        $nextIdRow = $this->db->fetchOne('SELECT posts_seq.NEXTVAL AS id FROM dual');
        $id = (int) ($nextIdRow['id'] ?? 0);
        $this->db->execute(
            'INSERT INTO posts (id, topic_id, user_id, title, content, created_at)
             VALUES (:id, :topic_id, :user_id, :title, :content, SYSTIMESTAMP)',
            ['id' => $id, 'topic_id' => $topicId, 'user_id' => $userId, 'title' => $title, 'content' => $content]
        );

        return $id;
    }

    public function addAttachment(int $postId, string $fileName, string $storedName, int $size, string $mime): void
    {
        $nextIdRow = $this->db->fetchOne('SELECT attachments_seq.NEXTVAL AS id FROM dual');
        $id = (int) ($nextIdRow['id'] ?? 0);
        $this->db->execute(
            'INSERT INTO attachments (id, post_id, file_name, stored_name, file_size, mime_type, created_at)
             VALUES (:id, :post_id, :file_name, :stored_name, :file_size, :mime_type, SYSTIMESTAMP)',
            ['id' => $id, 'post_id' => $postId, 'file_name' => $fileName, 'stored_name' => $storedName, 'file_size' => $size, 'mime_type' => $mime]
        );
    }

    public function getAttachments(int $postId): array
    {
        return $this->db->fetchAll(
            'SELECT id, file_name, stored_name, file_size, mime_type, created_at FROM attachments WHERE post_id = :post_id ORDER BY id ASC',
            ['post_id' => $postId]
        );
    }
}
