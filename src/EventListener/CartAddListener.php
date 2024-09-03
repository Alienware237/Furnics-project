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
        $this->logger->info('Event listener triggered for article ' . $event->getArticleId());
        $articleId = $event->getArticleId();
        $userEmail = $event->getUserEmail();
        $quantity = $event->getDetail()['quantity'];
        $data = $event->getDetail();

        unset($data["quantity"]);

        $this->logger->info('Quantity of articles: ' . $quantity);
        //$this->logger->info('size of article: ' . $size);

        // Logic to add the article to the user's cart
        $user = $this->entityManager->getRepository(User::class)->findBy(['email' => $userEmail]);
        $article = $this->entityManager->getRepository(Article::class)->findOneBy(['articleId' => $articleId]);

        if (is_array($user)) {
            $user = $user[0];
        }

        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart) {
            // Handle the case where the cart does not exist
            $this->logger->error("Cart not found for user $userEmail.");
            return;
        }
        $this->logger->info('Finding CartId: '. $cart->getCartId());

        if (!$article) {
            // Handle the case where the article does not exist
            $this->logger->error("Article $articleId not found.");
            return;
        }

        $cartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy(['article' => $article, 'detailsOfChoice' => json_encode($data)]);
        //Check if The article Exist in the Cart and just increment the quantity
        if (!$cartItem) {
            $cartItem = new CartItem();
            if ($quantity) {
                $cartItem->setQuantity(intval($quantity));
            } else {
                $cartItem->setQuantity(1);
            }
        } else {
            if ($quantity) {
                $cartItem->setQuantity($cartItem->getQuantity() + intval($quantity));
            } else {
                $cartItem->setQuantity($cartItem->getQuantity() + 1);
            }
        }

        // Create a new CartItem and set its attributes
        $cartItem->setArticle($article); // Set the Article entity
        $cartItem->setDetailsOfChoice(json_encode($data)); // Set details of choice if needed

        // Add the CartItem to the Cart
        $cart->addCartItem($cartItem);

        $this->entityManager->persist($cart);
        $this->entityManager->flush();
        //$this->cartItemManager->newCartItem($cart, $article, 1, '');
        $this->logger->info("Article $articleId added to cart for user $userEmail.");

        $this->logger->error("Failed to add article $articleId to cart for user $userEmail.");
    }
}
