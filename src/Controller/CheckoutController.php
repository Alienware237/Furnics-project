<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Article;
use okpt\furnics\project\Entity\Orders;
use okpt\furnics\project\Entity\Payment;
use okpt\furnics\project\Entity\User;
use okpt\furnics\project\Event\OrderEvent;
use okpt\furnics\project\Form\DeliveryAddressType;
use okpt\furnics\project\Form\SummaryType;
use okpt\furnics\project\Services\AddressChecker;
use okpt\furnics\project\Services\CartManager;
use okpt\furnics\project\Services\MailService;
use okpt\furnics\project\Services\UserManager;
use okpt\furnics\project\Services\OrdersManager;
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
    private $ordersManager;
    private $entityManager;
    private $workflow;
    private $logger;
    private $dispatcher;
    private $addressChecker;
    private $mailService;

    public function __construct(
        UserManager $userManager,
        CartManager $cartManager,
        OrdersManager $ordersManager,
        EntityManagerInterface $entityManager,
        WorkflowInterface $ordersProcessStateMachine,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher,
        AddressChecker $addressChecker,
        MailService $mailService
    ) {
        $this->userManager = $userManager;
        $this->cartManager = $cartManager;
        $this->ordersManager = $ordersManager;
        $this->entityManager = $entityManager;
        $this->workflow = $ordersProcessStateMachine;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->addressChecker = $addressChecker;
        $this->mailService = $mailService;
    }
    #[Route('/checkout-old', name: 'app_checkout')]
    public function index(): Response
    {
        $user = $this->getUser();

        $user = $this->userManager->getUserbyEmail($user->getUserIdentifier());

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

        if(!$user) {
            return $this->redirectToRoute('app_index');
        }
        $user = $this->userManager->getUserbyEmail($user->getUserIdentifier());
        $order = $this->ordersManager->getOpenOrder($user) ?? new Orders();
        $this->logger->info('finding Order: ' . $order->getOrderId());

        if (!$order) {
            throw $this->createNotFoundException('Order not found.');
        }

        switch ($order->getCurrentPlace()) {
            case 'shopping_cart':
                $this->logger->info('Switch case: shopping_cart');
                return $this->redirectToRoute('app_cart');
            case 'delivery_address':
                return $this->redirectToRoute('checkout_delivery_address');
            case 'summary_for_purchase':
                return $this->handleSummary($request, $order);
            case 'send_mail':
                // Call the mail service to send an email
                return $this->redirectToRoute('app_order_mail');
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
        if(!$user) {
            return $this->redirectToRoute('app_index');
        }
        $user = $this->userManager->getUserbyEmail($user->getUserIdentifier());
        $order = $this->ordersManager->getCurrentOrder($user, 'delivery_address') ?? new Orders();

        $cart = $this->cartManager->getCart($user);
        $cart = is_array($cart) ? $cart[0] : $cart;
        $allCartItems = $this->cartManager->getAllCartArticle($cart);

        if (sizeof($allCartItems) < 1) {
            $this->logger->info("No Article items found!");
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
                $email = $form->get('email')->getData();
                $firstName = $form->get('name')->getData();
                $phone = $form->get('phone')->getData();
                $country = $form->get('country')->getData();
                $city = $form->get('city')->getData();
                $street = $form->get('street')->getData();
                $houseNumber = $form->get('houseNumber')->getData();

                $user->setEmail($email);
                $user->setFirstName($firstName);
                $user->setPhone($phone);
                $user->setCountry($country);
                $user->setCity($city);
                $user->setStreet($street);
                $user->setHouseNumber($houseNumber);
                $this->entityManager->persist($user);
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
        //print_r("handleSummary!");
        $user = $this->getUser();
        $user = $this->userManager->getUserbyEmail($user->getUserIdentifier());
        $cart = $this->cartManager->getCart($user);
        $cart = is_array($cart) ? $cart[0] : $cart;
        $allCartItems = $this->cartManager->getAllCartArticle($cart);
        $order = $this->ordersManager->getCurrentOrder($user, 'summary_for_purchase') ?? new Orders();

        if (!$order) {
            throw $this->createNotFoundException('Order not found.');
        }

        $form = $this->createForm(SummaryType::class, $order);
        $form->handleRequest($request);

        $this->logger->info('Current order state: ' . $order->getCurrentPlace());

        $quantity = 0;
        $totalPrice = 0;

        foreach ($allCartItems as $item) {
            $quantity += $item['quantity'];
            $this->logger->info("Item: ");
            $this->logger->debug(json_encode($item['article']));
            $unitPrice = $item['article']->getArticlePrice();
            $totalPrice += $unitPrice * $item['quantity'];
        }

        //print_r('$totalPrice: '. $totalPrice);

        if ($form->isSubmitted()) {
            $this->logger->info("Form was submitted!!!");
            if ($form->isValid()) {
                $this->logger->info('ist going to place order because of form!!!');
                $order->setNextTransition('place_order');
                $this->dispatcher->dispatch(new OrderEvent($order), OrderEvent::NAME);
                $order->setTotalAmount($totalPrice);
                $this->entityManager->persist($order);

                //print_r($order->getCurrentPlace());

                $this->logger->info('Before redirecting to PayPal!!!');
                return $this->redirectToRoute('app_pay_pal', [
                    'total' => $order->getTotalAmount(),
                    'currency' => 'EUR',
                    'payment_description' => 'You have pay ' . $quantity . ' Articles by Kimpa'
                ]);
            }
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

    #[Route('/order', name: 'get_order', methods: ['GET'])]
    public function getOrder(): Response
    {
        // Retrieve or create an Orders entity
        $user = $this->getUser();

        $user = $this->userManager->getUserbyEmail($user->getUserIdentifier());
        $order = $this->entityManager->getRepository(Orders::class)->findOneBy(['user' => $user]) ?? new Orders();

        if (!$order) {
            throw $this->createNotFoundException('Order not found.');
        }

        $form = $this->createForm(SummaryType::class, $order);


        $cart = $this->cartManager->getCart($user);
        if (is_array($cart)) {
            $cart = $cart[0];
        }
        $allCartItems = $this->cartManager->getAllCartArticle($cart);

        return $this->render('mail/order-detail.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'allCartItems' => $allCartItems,
            'order' => $order,
        ]);
    }
}
