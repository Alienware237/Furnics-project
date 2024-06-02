<?php

namespace okpt\furnics\project\Services;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\CartItem;

class CartItemManager
{

    private $cartItemManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->cartItemManager = $entityManager;
    }

    public function newCartItem(int $cartId, int $articleId, int $quantity, string $detailOfChoise)
    {
        $cartItem = new CartItem();
        $cartItem->setCartId($cartId);
        $cartItem->setProductId($articleId);
        $cartItem->setQuantity($quantity);
        $cartItem->setDetailsOfChoice($detailOfChoise);
        $cartItem->setCreatedAt(new \DateTime());
        $cartItem->setUpdatedAt(new \DateTime());
        $this->cartItemManager->persist($cartItem);
        $this->cartItemManager->flush();
        return $cartItem;
    }

    public function removeCartItem(CartItem $cartItem) {
        $this->cartItemManager->remove($cartItem);
    }

    public function getCartItemById(int $cart_item_id) {
        return $this->cartItemManager->getRepository(CartItem::class)->findBy(['cartItemId' => $cart_item_id]);
    }

    public function getCartItemByCartId(int $cart_id) {
        return $this->cartItemManager->getRepository(CartItem::class)->findBy(['cartId' => $cart_id]);
    }
}
