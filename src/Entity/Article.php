<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Query\Mysql\Date;
use okpt\furnics\project\Repository\ArticleRepository;

#[ORM\Entity]
#[ApiResource]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', unique: true)]
    private ?int $articleId = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $articleName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $description;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $articlePrice = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $articleCategory = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $articleImages = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTimeInterface $updatedAt;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: CartItem::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private $cartItems;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: OrderItem::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private $orderItems;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private $comments;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Review::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private $reviews;

    private array $sizeAndQuantities = [];

    private string $categoryDescription = '';

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters and Setters

    // Article Id
    public function getArticleId(): ?int
    {
        return $this->articleId;
    }

    // Article Name
    public function getArticleName(): ?string
    {
        return $this->articleName;
    }

    public function setArticleName(string $articleName): self
    {
        $this->articleName = $articleName;
        return $this;
    }

    // Description
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    // Article Price
    public function getArticlePrice(): ?float
    {
        return $this->articlePrice;
    }

    public function setArticlePrice(float $articlePrice): self
    {
        $this->articlePrice = $articlePrice;
        return $this;
    }

    // Article Category
    public function getArticleCategory(): ?string
    {
        return $this->articleCategory;
    }

    public function setArticleCategory(?string $articleCategory): self
    {
        $this->articleCategory = $articleCategory;
        return $this;
    }

    // Article Images
    public function getArticleImages(): ?string
    {
        return $this->articleImages;
    }

    public function setArticleImages(?string $articleImages): self
    {
        $this->articleImages = $articleImages;
        return $this;
    }

    // Created At
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    // Updated At
    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    // Size and Quantities
    public function getSizeAndQuantities(): array
    {
        return $this->sizeAndQuantities;
    }

    public function setSizeAndQuantities(array $sizeAndQuantities): self
    {
        $this->sizeAndQuantities = $sizeAndQuantities;
        return $this;
    }

    // Category Description
    public function getCategoryDescription(): string
    {
        return $this->categoryDescription;
    }

    public function setCategoryDescription(string $categoryDescription): self
    {
        $this->categoryDescription = $categoryDescription;
        return $this;
    }

    public function getComment(): Collection
    {
        return $this->comments;
    }
    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setArticle($this);
        }
        return $this;
    }

    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setArticle($this);
        }
        return $this;
    }
}
