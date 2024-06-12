<?php

namespace okpt\furnics\project\Services;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\User;
use Psr\Log\LoggerInterface;

class UserManager {

    private $entityManager;
    private $cartManager;
    private $ordersManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, CartManager $cartManager, OrdersManager $ordersManager)
    {
        $this->entityManager = $entityManager;
        $this->cartManager = $cartManager;
        $this->ordersManager = $ordersManager;
        $this->logger = $logger;
    }

    public function newUser(string $firstName, string $lastName, string $email, string $password, string $salutation, string $street, int $houseNumber, string $zipCode, string $city, string $country, string $phone, string $cookie): void
    {
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setSalutation($salutation);
        $user->setStreet($street);
        $user->setHouseNumber($houseNumber);
        $user->setZipCode($zipCode);
        $user->setCity($city);
        $user->setCountry($country);
        $user->setPhone($phone);
        $user->setRole(2);
        $user->setCookie($cookie);
        $user->getCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());


        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
    public function createUser(User $user): User
    {
        $this->entityManager->persist($user);

        $this->cartManager->createCart($user);
        $this->ordersManager->createOrder($user);
        $this->entityManager->flush();
        return $user;
    }

    public function updateUser(User $user): void {

        $user->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
    public function deleteUser(User $user): void {
        $this->entityManager->remove($user);
    }

    public function getUserById(int $id): User {
        return $this->entityManager->find(User::class, $id);
    }

    /**
     * @return array|object[]|User[]
     */
    public function getUserbyEmailAndPassWD(string $email): array|User|null
    {
        $users = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->logger->info('Just log the finding user!');
        $this->logger->debug(json_encode($users));
        return $users;
    }

    public function getUserbyCookie(string $cookie): array|null
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['cookie' => $cookie]);
    }

    public function getUserByEmailAndCookie(string $email, string $cookie) {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email, 'cookie' => $cookie]);
    }

    public function getUserByName(string $name): User {
        return $this->entityManager->find(User::class, $name);
    }
    public function getUserByCategory(string $email): User {
        return $this->entityManager->find(User::class, $email);
    }
    public function getAllUsers(): array {
        return $this->entityManager->getRepository(User::class)->findAll();
    }
}