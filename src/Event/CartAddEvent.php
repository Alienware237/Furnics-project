<?php

namespace okpt\furnics\project\Event;

class CartAddEvent
{
    public const NAME = 'cart.add';

    private $articleId;
    private $userEmail;

    public function __construct(int $articleId, string $email)
    {
        $this->articleId = $articleId;
        $this->userEmail = $email;
    }

    public function getArticleId(): int
    {
        return $this->articleId;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }
}
