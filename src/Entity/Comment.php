<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;

#[ORM\Entity]
#[ApiResource]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $commentId = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ["persist", "remove", "update"])]
    #[ORM\Column(type: 'integer')]
    #[ORM\JoinColumn(nullable: false)]
    private ?int $userId;

    #[ORM\Column(type: 'text')]
    private string $commentText;

    #[ORM\Column(type: 'text')]
    private string $userData;

    #[ORM\ManyToOne(targetEntity: Article::class, cascade: ["persist", "remove", "update"])]
    #[ORM\Column(type: 'integer')]
    #[ORM\JoinColumn(nullable: false)]
    private int $articleId;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $updatedAt;

    public function getCommentId(): ?int
    {
        return $this->commentId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getArticleId(): int
    {
        return $this->articleId;
    }

    public function setArticleId(int $articleId): self
    {
        $this->articleId = $articleId;

        return $this;
    }

    public function getCommentText(): string
    {
        return $this->commentText;
    }

    public function setCommentText(string $commentText): self
    {
        $this->commentText = $commentText;

        return $this;
    }

    public function getUserData(): string
    {
        return $this->userData;
    }

    public function setUserData(string $userData): self
    {
        $this->userData = $userData;

        return $this;
    }

    public function getCreatedAt(): DateTime {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdDate): static {
        $this->createdAt = $createdDate;
        return $this;
    }

    public function getUpdatedAt(): DateTime {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedDate): static {
        $this->createdAt = $updatedDate;
        return $this;
    }
}
