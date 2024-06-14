<?php

namespace okpt\furnics\project\Services;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Orders;
use okpt\furnics\project\Entity\User;
use Psr\Log\LoggerInterface;

class OrdersManager
{
    private $ordersManager;

    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->ordersManager = $entityManager;
        $this->logger = $logger;
    }

    public function createOrder(User $user): void
    {
        $order = new Orders();
        $order->setHouseNumber($user->getHouseNumber());
        $order->setStreet($user->getStreet());
        $order->setCity($user->getCity());
        $order->setCountry($user->getCountry());
        $order->setPhone($user->getPhone());
        $order->setEmail($user->getEmail());
        $order->setName($user->getFirstName() . ' ' . $user->getLastName());
        $order->setUser($user);

        $this->ordersManager->persist($order);
        $this->ordersManager->flush();
    }

    public function getOrder(User $user): array
    {
        $this->logger->info(json_encode($user));
        return $this->ordersManager->getRepository(Orders::class)->findBy(['user' => $user]);
    }
}
