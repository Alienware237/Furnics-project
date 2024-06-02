<?php

namespace okpt\furnics\project\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Article;
use okpt\furnics\project\Entity\Cart;
use okpt\furnics\project\Entity\CartItem;
use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Event\CartAddEvent;
use okpt\furnics\project\Services\CartItemManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class CartAddListener
{
    private $entityManager;

    private $cartItemManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, CartItemManager $cartItemManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->cartItemManager = $cartItemManager;
        $this->logger = $logger;
    }

    #[AsEventListener(event: CartAddEvent::NAME)]
    public function onCartAddEvent($event): void
    {
        $this->logger->info('The event are successfully Calling! '. $event->getArticleId());
        $articleId = $event->getArticleId();
        $userEmail = $event->getUserEmail();

        // Logic to add the article to the user's cart
        $user = $this->entityManager->getRepository(User::class)->findBy(['email' => $userEmail]);

        if (is_array($user)) {
            $user = $user[0];
        }

        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['userId' => $user->getUserId()]);
        $this->logger->info('Finding CartId: '. $cart->getCartId());
        $cartItem = $this->cartItemManager->newCartItem($cart->getCartId(), $articleId, 1, '');
        $article = $this->entityManager->getRepository(Article::class)->find($articleId);

        if ($article) {
            $this->entityManager->persist($cartItem);
            $this->entityManager->flush();
            $this->logger->info("Article $articleId added to cart for user $userEmail.");
        } else {
            $this->logger->error("Failed to add article $articleId to cart for user $userEmail.");
        }
    }
}
