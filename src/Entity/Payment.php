<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use FontLib\Table\Type\name;

#[ORM\Entity]
#[ApiResource]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', unique: true)]
    private ?int $paymentId = null;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $datum;

    #[ORM\Column(type: 'string', length: 255)]
    private string $paymentArt;

    #[ORM\Column(type: 'float')]
    private float $amount;

    #[ORM\OneToOne(targetEntity: Orders::class)]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: "order_id", nullable: false)]
    private Orders $order;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: "user_id", nullable: false)]
    private ?User $user = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->datum = new DateTime();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getPaymentId(): ?int
    {
        return $this->paymentId;
    }

    public function getDatum(): DateTimeInterface
    {
        return $this->datum;
    }

    public function setDatum(DateTimeInterface $datum): self
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

    public function getOrder(): Orders
    {
        return $this->order;
    }

    public function setOrder(Orders $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
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
