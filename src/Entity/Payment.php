<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $paymentId;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $datum;

    #[ORM\Column(type: 'string', length: 255)]
    private string $paymentArt;

    #[ORM\Column(type: 'float')]
    private float $amount;

    #[ORM\OneToOne(targetEntity: Order::class, cascade: ["persist", "remove", "update"])]
    #[ORM\Column(type: 'integer')]
    #[ORM\JoinColumn(nullable: false)]
    private int $orderId;

    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $updatedAt;

    public function getPaymentId(): ?int
    {
        return $this->paymentId;
    }

    public function setPaymentId(int $paymentId): self
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getDatum(): \DateTimeInterface
    {
        return $this->datum;
    }

    public function setDatum(\DateTimeInterface $datum): self
    {
        $this->datum = $datum;

        return $this;
    }

    public function getPaymentArt(): string
    {
        return $this->paymentArt;
    }

    public function setPaymentArt(string $paymentArt): self
    {
        $this->paymentArt = $paymentArt;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
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
