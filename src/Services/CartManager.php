<?php

namespace okpt\furnics\project\Services;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Article;
use okpt\furnics\project\Entity\Cart;
use okpt\furnics\project\Entity\CartItem;
use Psr\Log\LoggerInterface;

class CartManager
{
    private $cartManager;
    private $entityManager;

    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->cartManager = $entityManager;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function createCart(int $UserId)
    {
        $cart = new Cart();
        $cart->setUserId(intval($UserId));
        $cart->setCreatedAt(new \DateTime());
        $cart->setUpdatedAt(new \DateTime());

        $this->cartManager->persist($cart);
        $this->cartManager->flush();
    }

    public function getCart(int $user_id): array
    {
        $this->logger->info('Find Cart of user: ' . $user_id);
        return $this->cartManager->getRepository(Cart::class)->findBy(['userId' => intval($user_id)]);
    }

    public function removeCart(int $user_id) {
        $cart = $this->getCart($user_id);
        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $this->cartManager->remove($cart);
    }

    public function getAllCartArticle(int $cartId): array {
        $allCartItem = [];
        $cartItems = $this->entityManager->getRepository(CartItem::class)->findBy(['cartId' => $cartId]);

        foreach ($cartItems as $cartItem) {
            $article = $this->entityManager->getRepository(Article::class)->find($cartItem->getarticleId());
            $allCartItem[] = array("article" => $article, "quantity" => $cartItem->getQuantity(), "detail" => $cartItem->getDetailsOfChoice());
        }

        return $allCartItem;
    }

}