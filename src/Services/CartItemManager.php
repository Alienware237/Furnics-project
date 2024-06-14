<?php

namespace okpt\furnics\project\Services;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Article;
use okpt\furnics\project\Entity\Cart;
use okpt\furnics\project\Entity\CartItem;
use okpt\furnics\project\Repository\CartItemRepository;

class CartItemManager
{
    private $cartItemManager;
    private $cartItemRepository;

    public function __construct(EntityManagerInterface $entityManager, CartItemRepository $cartItemRepository)
    {
        $this->cartItemManager = $entityManager;
        $this->cartItemRepository = $cartItemRepository;
    }

    public function newCartItem(Cart $cart, Article $article, int $quantity, string $detailOfChoise)
    {
        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setArticle($article);
        $cartItem->setQuantity($quantity);
        $cartItem->setDetailsOfChoice($detailOfChoise);
        $this->cartItemManager->persist($cartItem);
        $this->cartItemManager->flush();
    }

    public function removeCartItem(CartItem $cartItem)
    {
        $this->cartItemManager->remove($cartItem);
    }

    public function removeAllCartItem(Cart $cart)
    {
        $this->cartItemRepository->deleteAllCartItem($cart);
    }

    public function getCartItemById(int $cart_item_id)
    {
        return $this->cartItemManager->getRepository(CartItem::class)->findBy(['cartItemId' => $cart_item_id]);
    }

    public function getCartItemByCartId(Cart $cart)
    {
        return $this->cartItemManager->getRepository(CartItem::class)->findBy(['cart' => $cart]);
    }
}
