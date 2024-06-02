<?php

namespace okpt\furnics\project\Controller;

use okpt\furnics\project\Event\CartAddEvent;
use okpt\furnics\project\Services\CartItemManager;
use okpt\furnics\project\Services\CartManager;
use okpt\furnics\project\Services\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class CartController extends AbstractController
{
    private $userManager;
    private $cartManager;
    private $cartItemManager;
    private $logger;

    public function __construct(UserManager $userManager, CartManager $cartManager, CartItemManager $cartItemManager, LoggerInterface $logger) {
        $this->userManager = $userManager;
        $this->cartManager = $cartManager;
        $this->cartItemManager = $cartItemManager;
        $this->logger = $logger;
    }
    #[Route('/cart', name: 'app_cart')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = $this->getUser();

        $user = $this->userManager->getUserbyEmailAndPassWD($user->getUserIdentifier());

        $cart = $this->cartManager->getCart($user->getUserId());
        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $allCartItems = $this->cartManager->getAllCartArticle($cart->getCartId());

        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'user' => $user,
            'allCartItems' => $allCartItems

        ]);
    }

    #[Route('/cart/add', name: 'cart_add', methods: ['POST'])]
    public function addToCart(Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $articleId = $request->request->get('article_id');
        $user = $this->getUser();

        $this->logger->info('UserIdentifier for Event: ' . $user->getUserIdentifier() . ' /n' . 'ArticleId: ' . $articleId);

        if ($articleId) {
            $event = new CartAddEvent((int) $articleId, $user->getUserIdentifier());
            $eventDispatcher->dispatch($event, CartAddEvent::NAME);

            $this->redirectToRoute('app_index');
        }

        return $this->json(['status' => 'Failed to add article to cart'], Response::HTTP_BAD_REQUEST);
    }
}
