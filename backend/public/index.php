<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

use App\Controllers\AuthController;
use App\Controllers\PostController;
use App\Controllers\TopicController;
use App\Repositories\CommentRepository;
use App\Repositories\LikeRepository;
use App\Repositories\PostRepository;
use App\Repositories\TopicRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Utils\Database;

header('Access-Control-Allow-Origin: ' . env('CORS_ORIGIN', '*'));
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $db = new Database();
    $users = new UserRepository($db);
    $topics = new TopicRepository($db);
    $posts = new PostRepository($db);
    $comments = new CommentRepository($db);
    $likes = new LikeRepository($db);
    $auth = new AuthService($users);

    $authController = new AuthController($auth);
    $topicController = new TopicController($topics);
    $postController = new PostController($topics, $posts, $comments, $likes, $auth);

    $method = $_SERVER['REQUEST_METHOD'];
    $uri = strtok($_SERVER['REQUEST_URI'], '?');

    if ($method === 'POST' && $uri === '/api/auth/login') {
        $authController->login();
        exit;
    }

    if ($method === 'GET' && $uri === '/api/auth/me') {
        $authController->me();
        exit;
    }

    if ($method === 'GET' && $uri === '/api/topics') {
        $topicController->index();
        exit;
    }

    if (preg_match('#^/api/topics/([a-zA-Z0-9_-]+)/posts$#', $uri, $matches)) {
        if ($method === 'GET') {
            $postController->index($matches[1]);
            exit;
        }
        if ($method === 'POST') {
            $postController->create($matches[1]);
            exit;
        }
    }

    if ($method === 'POST' && preg_match('#^/api/posts/(\d+)/comments$#', $uri, $matches)) {
        $postController->comment((int) $matches[1]);
        exit;
    }

    if ($method === 'POST' && preg_match('#^/api/posts/(\d+)/likes$#', $uri, $matches)) {
        $postController->like((int) $matches[1]);
        exit;
    }

    if ($method === 'DELETE' && preg_match('#^/api/posts/(\d+)/likes$#', $uri, $matches)) {
        $postController->unlike((int) $matches[1]);
        exit;
    }

    jsonResponse(['message' => 'Not found'], 404);
} catch (Throwable $e) {
    jsonResponse(['message' => $e->getMessage()], 500);
}
