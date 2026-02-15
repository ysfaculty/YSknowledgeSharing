<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\TopicRepository;

class TopicController
{
    public function __construct(private readonly TopicRepository $topics)
    {
    }

    public function index(): void
    {
        jsonResponse(['topics' => $this->topics->listAll()]);
    }
}
