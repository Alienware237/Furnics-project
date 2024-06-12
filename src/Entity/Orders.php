<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', unique: true, nullable: false)]
    private ?int $orderId = null;

    #[ORM\OneToOne(inversedBy: 'orders', targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: "user_id", nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $orderDate;

    #[ORM\Column(type: 'float')]
    private float $totalAmount = 0;

    #[ORM\Column(type: Types::STRING, nullable: false)]
    private string $currentPlace = 'shopping_cart'; // Initial state

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private $orderItems;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $country;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $city;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $street;

    #[ORM\Column(type: 'integer', nullable: true)]
    private int $houseNumber;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $phone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $name;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $taxNumber = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $updatedAt;

    private $nextTransition;

    public function __construct()
    {
        $this->orderDate = new DateTime();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
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

    public function getOrderDate(): \DateTimeInterface
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeInterface $orderDate): self
    {
        $this->orderDate = $orderDate;
        return $this;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $telephone): self
    {
        $this->phone = $telephone;
        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;
        return $this;
    }

    public function getHouseNumber(): int
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(int $houseNumber): self
    {
        $this->houseNumber = $houseNumber;
        return $this;
    }

    public function getTaxNumber(): ?string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(?string $taxNumber): self
    {
        $this->taxNumber = $taxNumber;
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

    public function getCurrentPlace(): string
    {
        return $this->currentPlace;
    }

    public function setCurrentPlace(string $currentPlace): self
    {
        $this->currentPlace = $currentPlace;
        return $this;
    }

    public function setNextTransition(string $transition): void
    {
        $this->nextTransition = $transition;
    }

    public function getNextTransition(): string
    {
        return $this->nextTransition;
    }

}