<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Event\OrderEvent;
use okpt\furnics\project\Form\SummaryType;
use okpt\furnics\project\Services\MailService;
use okpt\furnics\project\Services\UserManager;
use okpt\furnics\project\Services\OrdersManager;
use okpt\furnics\project\Services\CartManager;
use okpt\furnics\project\Services\CartItemManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MailController extends AbstractController
{
    private $mailService;
    private $userManager;
    private $ordersManager;
    private $cartManager;
    private $cartItemManager;
    private $entityManager;
    private $dispatcher;

    public function __construct(
        MailService $mailService,
        UserManager $userManager,
        OrdersManager $ordersManager,
        CartManager $cartManager,
        CartItemManager $cartItemManager,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->mailService = $mailService;
        $this->userManager = $userManager;
        $this->ordersManager = $ordersManager;
        $this->cartManager = $cartManager;
        $this->cartItemManager = $cartItemManager;
        $this->entityManager = $entityManager;
        $this->dispatcher = $eventDispatcher;
    }
    #[Route('/mail', name: 'app_order_mail')]
    public function index(): Response
    {
        $user = $this->getUser();
        $user = $this->userManager->getUserbyEmail($user->getUserIdentifier());

        $order = $this->ordersManager->getCurrentOrder($user, 'send_mail') ?? new Orders();

        $form = $this->createForm(SummaryType::class, $order);

        $cart = $this->cartManager->getCart($user);
        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $allCartItems = $this->cartManager->getAllCartArticle($cart);

        $this->mailService->sendEmail(
            $user->getEmail(),
            'Order Confirmation',
            [
                'form' => $form->createView(),
                'user' => $user,
                'allCartItems' => $allCartItems,
                'order' => $order
            ]
        );

        $order->setNextTransition('proceed_to_place_order');
        $this->dispatcher->dispatch(new OrderEvent($order), OrderEvent::NAME);
        $this->entityManager->flush();

        // Remove all Items in the cart and create a new order for this user
        $this->cartItemManager->removeAllCartItem($cart);
        $this->userManager->newOrder($user);

        return $this->redirectToRoute('app_thankyou');
    }
}
