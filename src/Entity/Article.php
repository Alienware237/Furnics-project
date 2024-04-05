<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Query\Mysql\Date;
use okpt\furnics\project\Repository\ArticleRepository;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ApiResource]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $articleId = null;

    #[ORM\Column(length: 255)]
    private ?string $articleName = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $articlePrice = null;

    #[ORM\Column]
    private ?int $numberInStock = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $articleCategory = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $articleImages = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $updatedAt;

    public function getArticleId(): ?int
    {
        return $this->articleId;
    }

    public function setArticleId(int $articleId): static
    {
        $this->articleId = $articleId;

        return $this;
    }

    public function getArticleName(): ?string
    {
        return $this->articleName;
    }

    public function setArticleName(string $articleName): static
    {
        $this->articleName = $articleName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getArticlePrice(): ?string
    {
        return $this->articlePrice;
    }

    public function setArticlePrice(string $articlePrice): static
    {
        $this->articlePrice = $articlePrice;

        return $this;
    }

    public function getNumberInStock(): ?int
    {
        return $this->numberInStock;
    }

    public function setNumberInStock(int $numberInStock): static
    {
        $this->numberInStock = $numberInStock;

        return $this;
    }

    public function getArticleCategory(): ?string
    {
        return $this->articleCategory;
    }

    public function setArticleCategory(?string $articleCategory): static
    {
        $this->articleCategory = $articleCategory;

        return $this;
    }

    public function getArticleImages(): ?array
    {
        return $this->articleImages;
    }

    public function setArticleImages(?array $articleImages): static
    {
        $this->articleImages = $articleImages;

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
