<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
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

    #[ORM\ManyToOne(targetEntity: Cart::class, cascade: ["persist", "remove", "update"])]
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\JoinColumn(nullable: false)]
    private int $cartId;

    #[ORM\OneToOne(targetEntity: Article::class, cascade: ["persist", "remove", "update"])]
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\JoinColumn(nullable: false)]
    private int $articleId;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $quantity;

    #[ORM\Column(type: 'string', length: 255)]
    private string $detailsOfChoice;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $updatedAt;

    public function getCartItemId(): ?int
    {
        return $this->cartItemId;
    }

    public function getCartId(): int
    {
        return $this->cartId;
    }

    public function setCartId(int $cartId): self
    {
        $this->cartId = $cartId;

        return $this;
    }

    public function getarticleId(): int
    {
        return $this->articleId;
    }

    public function setProductId(int $articleId): self
    {
        $this->articleId = $articleId;

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
        $this->updatedAt = $updatedDate;
        return $this;
    }
}
