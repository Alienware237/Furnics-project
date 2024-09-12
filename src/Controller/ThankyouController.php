<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Orders;
use okpt\furnics\project\Services\CartItemManager;
use okpt\furnics\project\Services\CartManager;
use okpt\furnics\project\Services\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

class ThankyouController extends AbstractController
{
    private $entityManager;
    private $userManager;
    private $cartManager;
    private $cartItemManager;
    private $workflow;

    public function __construct(EntityManagerInterface $entityManager, UserManager $userManager, CartManager $cartManager, CartItemManager $cartItemManager, WorkflowInterface $ordersProcessStateMachine)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->cartManager = $cartManager;
        $this->cartItemManager = $cartItemManager;
        $this->workflow = $ordersProcessStateMachine;
    }

    #[Route('/thankyou', name: 'app_thankyou')]
    public function index(): Response
    {
        $user = $this->getUser();
        $user = $this->userManager->getUserbyEmail($user->getUserIdentifier());
        $cart = $this->cartManager->getCart($user);
        $cart = is_array($cart) ? $cart[0] : $cart;

        $this->cartItemManager->removeAllCartItem($cart);

        $order = $this->entityManager->getRepository(Orders::class)->findOneBy(['user' => $user]);

        $order->setCurrentPlace('shopping_cart');
        //$this->workflow->apply($order, 'proceed_to_delivery_address');
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        //$order->setNextTransition('proceed_to_summary');
        return $this->render('thankyou/index.html.twig', [
            'controller_name' => 'ThankyouController',
        ]);
    }
}
