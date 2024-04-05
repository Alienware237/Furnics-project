<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use mysql_xdevapi\TableDelete;

#[ORM\Entity]
#[ApiResource]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $cartId = null;

    #[ORM\OneToOne(mappedBy: Cart::class, targetEntity: User::class, cascade: ["persist", "remove", "update"])]
    #[ORM\Column(type: 'integer')]
    #[ORM\JoinColumn(nullable: false)]
    private int $userId;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $updatedAt;
    public function getCartId(): ?int
    {
        return $this->cartId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

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
