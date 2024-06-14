<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Orders;
use okpt\furnics\project\Entity\Payment;
use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Event\OrderEvent;
use okpt\furnics\project\Form\DeliveryAddressType;
use okpt\furnics\project\Form\SummaryType;
use okpt\furnics\project\Services\AddressChecker;
use okpt\furnics\project\Services\CartManager;
use okpt\furnics\project\Services\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

class CheckoutController extends AbstractController
{
    private $userManager;
    private $cartManager;
    private $entityManager;
    private $workflow;
    private $logger;
    private $dispatcher;
    private $addressChecker;

    public function __construct(UserManager $userManager, CartManager $cartManager, EntityManagerInterface $entityManager, WorkflowInterface $ordersProcessStateMachine, LoggerInterface $logger, EventDispatcherInterface $dispatcher, AddressChecker $addressChecker)
    {
        $this->userManager = $userManager;
        $this->cartManager = $cartManager;
        $this->entityManager = $entityManager;
        $this->workflow = $ordersProcessStateMachine;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->addressChecker = $addressChecker;
    }
    #[Route('/checkout-old', name: 'app_checkout')]
    public function index(): Response
    {
        $user = $this->getUser();

        $user = $this->userManager->getUserbyEmailAndPassWD($user->getUserIdentifier());

        $cart = $this->cartManager->getCart($user);
        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $allCartItems = $this->cartManager->getAllCartArticle($cart);
        return $this->render('checkout/index.html.twig', [
            'controller_name' => 'CheckoutController',
            'user' => $user,
            'allCartItems' => $allCartItems

        ]);
    }

    #[Route('/checkout', name: 'checkout')]
    public function index_checkout(Request $request)
    {
        $user = $this->getUser();
        $user = $this->userManager->getUserbyEmailAndPassWD($user->getUserIdentifier());
        $order = $this->entityManager->getRepository(Orders::class)->findOneBy(['user' => $user]) ?? new Orders();

        switch ($order->getCurrentPlace()) {
            case 'shopping_cart':
                $this->logger->info('Switch case: shopping_cart');
                return $this->redirectToRoute('app_cart');
            case 'delivery_address':
                return $this->redirectToRoute('checkout_delivery_address');
            case 'summary_for_purchase':
                return $this->handleSummary($request, $order);
            case 'ordered':
                return $this->redirectToRoute('app_thankyou');
        }

        return new JsonResponse(
            ['error' => 'Resource not found'],
            JsonResponse::HTTP_NOT_FOUND
        );
    }

    #[Route('/checkout/delivery_address', name: 'checkout_delivery_address')]
    public function handleDeliveryAddress(Request $request): Response
    {
        $user = $this->getUser();
        $user = $this->userManager->getUserbyEmailAndPassWD($user->getUserIdentifier());
        $order = $this->entityManager->getRepository(Orders::class)->findOneBy(['user' => $user]) ?? new Orders();

        $cart = $this->cartManager->getCart($user);
        $cart = is_array($cart) ? $cart[0] : $cart;
        $allCartItems = $this->cartManager->getAllCartArticle($cart);

        if (sizeof($allCartItems) < 1) {
            return $this->redirectToRoute('checkout');
        }

        $form = $this->createForm(DeliveryAddressType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();
            $isEU = $this->addressChecker->isEU($data->getCountry());

            if ($isEU && empty($data->getTaxNumber())) {
                $form->addError(new FormError('Please provide your tax number.'));
            }
            if ($form->isValid()) {
                $order->setOrderDate(new \DateTime());
                $order->setNextTransition('proceed_to_summary');
                $this->dispatcher->dispatch(new OrderEvent($order), OrderEvent::NAME);
                $this->entityManager->flush();
                return $this->redirectToRoute('checkout');
            }
        }
        return $this->render('checkout/delivery_address.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'allCartItems' => $allCartItems,
            'include_tax_number' => true,
        ]);
    }

    private function handleSummary(Request $request): Response
    {
        $user = $this->getUser();
        $user = $this->userManager->getUserbyEmailAndPassWD($user->getUserIdentifier());
        $cart = $this->cartManager->getCart($user);
        $cart = is_array($cart) ? $cart[0] : $cart;
        $allCartItems = $this->cartManager->getAllCartArticle($cart);
        $order = $this->entityManager->getRepository(Orders::class)->findOneBy(['user' => $user]) ?? new Orders();

        if (!$order) {
            throw $this->createNotFoundException('Order not found.');
        }

        $form = $this->createForm(SummaryType::class, $order);
        $form->handleRequest($request);

        $this->logger->info('Current order state: ' . $order->getCurrentPlace());


        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info('ist not going to place order because of form: ' . $form->isSubmitted() && $form->isValid());
            $order->setNextTransition('place_order');
            $this->dispatcher->dispatch(new OrderEvent($order), OrderEvent::NAME);
            $order->setTotalAmount(0);
            $this->entityManager->persist($order);

            $allArticlesPrices = 0;
            foreach ($allCartItems as $articleItem) {
                $articlePrice = $articleItem['article']->getArticlePrice();
                $allArticlesPrices += ($articlePrice * $articleItem['quantity']);
            }

            $payment = new Payment();
            $payment->setUser($user);
            $payment->setOrder($order);
            $payment->setPaymentArt('Paypal');
            $payment->setAmount($allArticlesPrices);

            $this->entityManager->persist($payment);
            $this->entityManager->flush();
            return $this->redirectToRoute('checkout');
        }

        return $this->render('checkout/summary.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'user' => $user,
            'allCartItems' => $allCartItems
        ]);
    }

    private function renderCheckoutSummary(Request $request, Orders $order, $user): Response
    {
        $cart = $this->cartManager->getCart($user);
        $cart = is_array($cart) ? $cart[0] : $cart;
        $allCartItems = $this->cartManager->getAllCartArticle($cart);

        $form = $this->createForm(SummaryType::class, $order);
        $form->handleRequest($request);

        $this->logger->info('Current order state: ' . $order->getCurrentPlace());

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setNextTransition('place_order');
            $this->dispatcher->dispatch(new OrderEvent($order), OrderEvent::NAME);
            $this->entityManager->flush();
            return $this->redirectToRoute('checkout');
        }

        return $this->render('checkout/summary.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'user' => $user,
            'allCartItems' => $allCartItems
        ]);
    }

    private function getOrder(): Orders
    {
        // Retrieve or create an Orders entity
        $user = $this->getUser();

        $user = $this->userManager->getUserbyEmailAndPassWD($user->getUserIdentifier());
        return $this->entityManager->getRepository(Orders::class)->findOneBy(['user' => $user]);
    }
}
