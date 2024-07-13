<?php

namespace okpt\furnics\project\Event;

class CartAddEvent
{
    public const NAME = 'cart.add';

    private $articleId;
    private $userEmail;
    private $detail;

    public function __construct(int $articleId, string $email, $data = null)
    {
        $this->articleId = $articleId;
        $this->userEmail = $email;
        $this->detail = $data;
    }

    public function getArticleId(): int
    {
        return $this->articleId;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function getQuantity(): string
    {
        return $this->userEmail;
    }

    public function getDetail() {
        return $this->detail;
    }
}
