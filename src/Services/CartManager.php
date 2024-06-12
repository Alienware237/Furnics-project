<?php

namespace okpt\furnics\project\Services;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Article;
use okpt\furnics\project\Entity\Cart;
use okpt\furnics\project\Entity\CartItem;
use okpt\furnics\project\Entity\User;
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

    public function createCart(User $user): void
    {
        $cart = new Cart();
        $cart->setUser($user);

        $this->cartManager->persist($cart);
        $this->cartManager->flush();
    }

    public function getCart(User $user)
    {
        //$this->logger->info(json_encode($user));
        return $this->cartManager->getRepository(Cart::class)->findBy(['user' => $user]);
    }

    public function removeCart(User $user) {
        $cart = $this->getCart($user);
        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $this->cartManager->remove($cart);
    }

    public function getAllCartArticle(Cart $cart): array {
        $allCartItem = [];
        $cartItems = $this->entityManager->getRepository(CartItem::class)->findBy(['cart' => $cart]);

        foreach ($cartItems as $cartItem) {
            $article = $this->entityManager->getRepository(Article::class)->find($cartItem->getarticle());
            $allCartItem[] = array("article" => $article, "quantity" => $cartItem->getQuantity(), "detail" => $cartItem->getDetailsOfChoice());
        }

        return $allCartItem;
    }

}