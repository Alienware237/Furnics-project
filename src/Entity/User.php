<?php

namespace okpt\furnics\project\Entity;

use ApiPlatform\Metadata\ApiResource;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ApiResource]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', unique: true, nullable: false)]
    private ?int $userId = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $salutation = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $street = null;

    #[ORM\Column(type: 'integer')]
    private ?int $houseNumber = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $zipCode = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $city = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $country = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $role;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $cookie = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Cart::class, cascade: ['persist', 'remove'])]
    private Cart $cart;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Orders::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Orders $orders;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Review::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->role = 'user';
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->reviews = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getSalutation(): ?string
    {
        return $this->salutation;
    }

    public function setSalutation(?string $salutation): self
    {
        $this->salutation = $salutation;
        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;
        return $this;
    }

    public function getHouseNumber(): ?int
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?int $houseNumber): self
    {
        $this->houseNumber = $houseNumber;
        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getCookie(): ?string
    {
        return $this->cookie;
    }

    public function setCookie(?string $cookie): self
    {
        $this->cookie = $cookie;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getCart(): Collection
    {
        return $this->cart;
    }

    public function getOrder(): Collection
    {
        return $this->order;
    }

    public function getRoles(): array
    {
        return [$this->role];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        $this->password = null;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->createdAt = $created_at;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updatedAt = $updated_at;
        return $this;
    }

    public function signIn($user, $remember = false)
    {
        $this->setAuthenticated(true);
        $this->setAttribute('user_id', $user->getId());

        if ($remember) {
            $rememberKey = bin2hex(random_bytes(16));
            $user->setRememberKey($rememberKey);
            $this->save();

            $value = base64_encode(serialize([$rememberKey, $user->getUsername()]));
            sfContext::getInstance()->getResponse()->setCookie('MyWebSite', $value, time() + 3600 * 24 * 15, '/');
        }
    }

    public function signOut()
    {
        $this->setAuthenticated(false);
        sfContext::getInstance()->getResponse()->setCookie('MyWebSite', '', time() - 3600, '/');
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
