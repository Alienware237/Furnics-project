<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', unique: true, nullable: false)]
    private ?int $reviewId = null;

    #[ORM\Column(type: 'text')]
    private string $reviewText;

    #[ORM\Column(type: 'integer')]
    private int $rating;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'review')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: "user_id", nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'review')]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: "article_id", nullable: false)]
    private ?Article $article = null;

    #[ORM\Column(type: 'text')]
    private string $userData;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getReviewId(): ?int
    {
        return $this->reviewId;
    }

    public function getReviewText(): string
    {
        return $this->reviewText;
    }

    public function setReviewText(string $reviewText): self
    {
        $this->reviewText = $reviewText;
        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;
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

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
