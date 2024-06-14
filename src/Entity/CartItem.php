<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', unique: true, nullable: false)]
    private ?int $cartItemId = null;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'cartItems')]
    #[ORM\JoinColumn(name: 'cart_id', referencedColumnName: "cart_id", nullable: false)]
    private Cart $cart;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'cartItems')]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: "article_id", nullable: false)]
    private Article $article;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $quantity;

    #[ORM\Column(type: 'string', length: 255)]
    private string $detailsOfChoice;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getCartItemId(): ?int
    {
        return $this->cartItemId;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        $this->cart = $cart;
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

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getDetailsOfChoice(): string
    {
        return $this->detailsOfChoice;
    }

    public function setDetailsOfChoice(string $detailsOfChoice): self
    {
        $this->detailsOfChoice = $detailsOfChoice;
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
