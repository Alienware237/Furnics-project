<?php

namespace okpt\furnics\project\Controller;

use okpt\furnics\project\Event\CartAddEvent;
use okpt\furnics\project\Services\CartManager;
use okpt\furnics\project\Services\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class CartController extends AbstractController
{
    private $userManager;
    private $cartManager;
    private $logger;
    private $eventDispatcher;
    private $csrfTokenManager;

    public function __construct(UserManager $userManager, CartManager $cartManager, LoggerInterface $logger, CsrfTokenManagerInterface $csrfTokenManager, EventDispatcherInterface $eventDispatcher) {
        $this->userManager = $userManager;
        $this->cartManager = $cartManager;
        $this->logger = $logger;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->eventDispatcher = $eventDispatcher;
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
    public function addToCart(Request $request)
    {
        $articleId = $request->request->get('article_id');
        $user = $this->getUser();
        $submittedToken = $request->request->get('_csrf_token');

        $this->logger->info('UserIdentifier for AddEvent: ' . $user->getUserIdentifier() . '\n' . 'ArticleId: ' . $articleId);

        // Validate CSRF token
        if (!$this->isCsrfTokenValid('add-to-cart', $submittedToken)) {
            return $this->json(['status' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        if ($articleId) {
            $event = new CartAddEvent((int) $articleId, $user->getUserIdentifier());
            $this->eventDispatcher->dispatch($event, CartAddEvent::NAME);

            return $this->json(['status' => 'Article added to cart'], Response::HTTP_OK);
        }

        return $this->json(['status' => 'Failed to add article to cart'], Response::HTTP_BAD_REQUEST);
    }
}
