<?php

namespace okpt\furnics\project\Services;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Orders;
use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Repository\OrderRepository;
use Psr\Log\LoggerInterface;

class OrdersManager
{
    private $entityManager;
    private $ordersRepository;

    private $logger;

    public function __construct(EntityManagerInterface $entityManager, OrderRepository $orderRepository, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->ordersRepository = $orderRepository;
        $this->logger = $logger;
    }

    public function createOrder(User $user): Orders
    {
        $order = new Orders();
        $order->setHouseNumber($user->getHouseNumber());
        $order->setStreet($user->getStreet());
        $order->setCity($user->getCity());
        $order->setCountry($user->getCountry());
        $order->setPhone($user->getPhone());
        $order->setEmail($user->getEmail());
        $order->setName($user->getFirstName() . ' ' . $user->getLastName());

        return $order;
    }

    public function getOrder(User $user): array
    {
        $this->logger->info(json_encode($user));
        return $this->entityManager->getRepository(Orders::class)->findBy(['user' => $user]);
    }

    public function getCurrentOrder(User $user, $currentPlace)
    {
        return $this->ordersRepository->findCurrentOrder($user, $currentPlace);
    }

    public function getOpenOrder(User $user)
    {
        return $this->ordersRepository->findOpenOder($user);
    }
}
