<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CommentRepository;
use App\Repositories\LikeRepository;
use App\Repositories\PostRepository;
use App\Repositories\TopicRepository;
use App\Services\AuthService;

class PostController
{
    public function __construct(
        private readonly TopicRepository $topics,
        private readonly PostRepository $posts,
        private readonly CommentRepository $comments,
        private readonly LikeRepository $likes,
        private readonly AuthService $auth
    ) {
    }

    public function index(string $topicCode): void
    {
        $topic = $this->topics->findByCode($topicCode);
        if (!$topic) {
            jsonResponse(['message' => '존재하지 않는 주제입니다.'], 404);
            return;
        }

        $user = $this->resolveUser();
        $items = $this->posts->listByTopic((int) $topic['id']);
        foreach ($items as &$post) {
            $postId = (int) $post['id'];
            $post['attachments'] = $this->posts->getAttachments($postId);
            $post['comments'] = $this->comments->listByPost($postId);
            $post['like_count'] = (int) ($post['like_count'] ?? 0);
            $post['liked_by_me'] = $user ? $this->likes->hasUserLiked($postId, (int) $user['id']) : false;
        }

        jsonResponse(['topic' => $topic, 'posts' => $items]);
    }

    public function create(string $topicCode): void
    {
        $user = $this->requireUser();
        if (!$user) {
            return;
        }

        $topic = $this->topics->findByCode($topicCode);
        if (!$topic) {
            jsonResponse(['message' => '존재하지 않는 주제입니다.'], 404);
            return;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        if ($title === '' || $content === '') {
            jsonResponse(['message' => 'title/content는 필수입니다.'], 422);
            return;
        }

        $files = $_FILES['attachments'] ?? null;
        $count = $files && is_array($files['name']) ? count(array_filter($files['name'])) : 0;
        $maxAttachments = (int) env('MAX_ATTACHMENTS', '3');
        if ($count > $maxAttachments) {
            jsonResponse(['message' => "첨부파일은 최대 {$maxAttachments}개까지 업로드 가능합니다."], 422);
            return;
        }

        $postId = $this->posts->create((int) $topic['id'], (int) $user['id'], $title, $content);

        if ($files && is_array($files['name'])) {
            $uploadDir = __DIR__ . '/../../' . env('UPLOAD_DIR', 'storage/uploads');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $maxSize = ((int) env('MAX_UPLOAD_SIZE_MB', '10')) * 1024 * 1024;

            foreach ($files['name'] as $idx => $name) {
                if (!$name) {
                    continue;
                }

                $size = (int) $files['size'][$idx];
                if ($size > $maxSize) {
                    continue;
                }

                $tmp = $files['tmp_name'][$idx];
                $mime = (string) ($files['type'][$idx] ?? 'application/octet-stream');
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename((string) $name));
                $stored = uniqid('att_', true) . '_' . $safeName;
                $dest = $uploadDir . '/' . $stored;
                move_uploaded_file($tmp, $dest);
                $this->posts->addAttachment($postId, (string) $name, $stored, $size, $mime);
            }
        }

        jsonResponse(['message' => '등록되었습니다.', 'post_id' => $postId], 201);
    }

    public function comment(int $postId): void
    {
        $user = $this->requireUser();
        if (!$user) {
            return;
        }

        $post = $this->posts->findById($postId);
        if (!$post) {
            jsonResponse(['message' => '게시글을 찾을 수 없습니다.'], 404);
            return;
        }

        $payload = requestJson();
        $content = trim((string) ($payload['content'] ?? ''));
        if ($content === '') {
            jsonResponse(['message' => '댓글 내용을 입력하세요.'], 422);
            return;
        }

        $id = $this->comments->create($postId, (int) $user['id'], $content);
        jsonResponse(['message' => '댓글이 등록되었습니다.', 'comment_id' => $id], 201);
    }

    public function like(int $postId): void
    {
        $user = $this->requireUser();
        if (!$user) {
            return;
        }

        $post = $this->posts->findById($postId);
        if (!$post) {
            jsonResponse(['message' => '게시글을 찾을 수 없습니다.'], 404);
            return;
        }

        $this->likes->like($postId, (int) $user['id']);
        jsonResponse(['message' => '좋아요 반영', 'like_count' => $this->likes->countByPost($postId), 'liked_by_me' => true]);
    }

    public function unlike(int $postId): void
    {
        $user = $this->requireUser();
        if (!$user) {
            return;
        }

        $post = $this->posts->findById($postId);
        if (!$post) {
            jsonResponse(['message' => '게시글을 찾을 수 없습니다.'], 404);
            return;
        }

        $this->likes->unlike($postId, (int) $user['id']);
        jsonResponse(['message' => '좋아요 취소', 'like_count' => $this->likes->countByPost($postId), 'liked_by_me' => false]);
    }

    private function resolveUser(): ?array
    {
        $token = getAuthorizationBearer();
        if (!$token) {
            return null;
        }

        return $this->auth->currentUser($token);
    }

    private function requireUser(): ?array
    {
        $user = $this->resolveUser();
        if (!$user) {
            jsonResponse(['message' => 'Unauthorized'], 401);
            return null;
        }

        return $user;
    }
}
