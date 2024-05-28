<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Query\Mysql\Date;
use okpt\furnics\project\Repository\ArticleRepository;
//use phpDocumentor\Reflection\Types\Collection;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ApiResource]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', unique: true, nullable: false)]
    private ?int $articleId = null;

    #[ORM\Column(type:'string', length: 255)]
    private ?string $articleName = null;

    #[ORM\Column(type:'string', length: 255)]
    private ?string $description;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?int $articlePrice = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $numberInStock;

    #[ORM\Column(type:'string', length: 255, nullable: true)]
    private ?string $articleCategory = null;

    private array $sizeAndQuantities;

    private string $categoryDescription;

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

    public function setCreatedAt(DateTime $createdDate): void
    {
        $this->createdAt = $createdDate;
    }

    public function getUpdatedAt(): DateTime {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedDate): void
    {
        $this->updatedAt = $updatedDate;
    }

    public function getSizeAndQuantities(): array
    {
        return $this->sizeAndQuantities;
    }

    public function setSizeAndQuantities(array $sizeAndQuantities): self
    {
        $this->sizeAndQuantities = $sizeAndQuantities;
        return $this;
    }

    public function getCategoryDescription(): string {
        return $this->categoryDescription;
    }

    public function setCategoryDescription(string $categoryDescription): void
    {
        $this->categoryDescription = $categoryDescription;
    }
}
