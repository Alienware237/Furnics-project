<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Orders;
use okpt\furnics\project\Event\CartAddEvent;
use okpt\furnics\project\Event\OrderEvent;
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
use Symfony\Component\Workflow\WorkflowInterface;

class CartController extends AbstractController
{
    private $userManager;
    private $cartManager;

    private $entityManager;
    private $logger;
    private $eventDispatcher;
    private $csrfTokenManager;
    private $workflow;

    public function __construct(UserManager $userManager, CartManager $cartManager, EntityManagerInterface $entityManager, LoggerInterface $logger, CsrfTokenManagerInterface $csrfTokenManager, EventDispatcherInterface $eventDispatcher, WorkflowInterface $ordersProcessStateMachine)
    {
        $this->userManager = $userManager;
        $this->cartManager = $cartManager;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->workflow = $ordersProcessStateMachine;
    }
    #[Route('/cart', name: 'app_cart')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = $this->getUser();

        $user = $this->userManager->getUserbyEmailAndPassWD($user->getUserIdentifier());

        $cart = $this->cartManager->getCart($user);
        $cart = is_array($cart) ? $cart[0] : $cart;

        $allCartItems = $this->cartManager->getAllCartArticle($cart);

        $order = $this->entityManager->getRepository(Orders::class)->findOneBy(['user' => $user]) ?? new Orders();

        if (!$order) {
            throw $this->createNotFoundException('Order not found.');
        }
        if (sizeof($allCartItems) > 0) {
            $this->logger->info('Current order state: ' . $order->getCurrentPlace());

            $order->setNextTransition('proceed_to_delivery_address');
            // Dispatch the order event
            $event = new OrderEvent($order);
            $this->eventDispatcher->dispatch($event, OrderEvent::NAME);
            $this->entityManager->flush();
        }

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
