<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Orders;
use okpt\furnics\project\Entity\Payment as PaymentEntity;
use okpt\furnics\project\Event\OrderEvent;
use okpt\furnics\project\Form\SummaryType;
use okpt\furnics\project\Services\CartManager;
use okpt\furnics\project\Services\MailService;
use okpt\furnics\project\Services\Paypal\PayPalService;
use okpt\furnics\project\Services\UserManager;
use okpt\furnics\project\Services\OrdersManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayPalController extends AbstractController
{
    private $paypalService;
    private $userManager;
    private $entityManager;
    private $mailService;
    private $cartManager;
    private $ordersManager;
    private $dispatcher;
    private $logger;

    public function __construct(
        PayPalService $paypalService,
        MailService $mailService,
        EntityManagerInterface $entityManager,
        UserManager $userManager,
        CartManager $cartManager,
        OrdersManager $ordersManager,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
        $this->paypalService = $paypalService;
        $this->mailService = $mailService;
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->cartManager = $cartManager;
        $this->ordersManager = $ordersManager;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    #[Route('/pay/pal', name: 'app_pay_pal')]
    public function pay(Request $request): Response
    {
        $this->logger->info("Initiating PayPal payment");

        $totalAmount = $request->query->get('total', 1);
        $currency = $request->query->get('currency', 'USD');
        $paymentDescription = $request->query->get('payment_description', 'Payment for Order');

        try {
            // Generate absolute URLs for success and cancel
            $successUrl = $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $cancelUrl = $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);


            // Create PayPal payment
            $payment = $this->paypalService->createPayment(
                1.0,
                $currency,
                $paymentDescription,
                $successUrl,
                $cancelUrl
            );

            // Retrieve approval link
            foreach ($payment['links'] as $link) {
                if ($link['rel'] === 'approval_url') {
                    $this->logger->info('redirect to approval url');
                    $this->logger->info($link['href']);
                    return $this->redirect($link['href']);
                }
            }

            throw new \Exception('Approval link not found in PayPal response.');
        } catch (\Exception $e) {
            $this->logger->error('PayPal payment initiation failed: ' . $e->getMessage());
            return $this->redirectToRoute('payment_cancel');
        }
    }

    #[Route("/payment-success", name: 'payment_success')]
    public function paymentSuccess(Request $request): Response
    {
        $paymentId = $request->query->get('paymentId');
        $payerId = $request->query->get('PayerID');

        if (!$paymentId || !$payerId) {
            $this->logger->error('Invalid payment data: missing paymentId or payerId');
            return $this->redirectToRoute('payment_cancel');
        }

        try {
            // Execute the payment
            $this->paypalService->executePayment($paymentId, $payerId);

            // Handle successful payment logic
            $this->logger->info('PayPal payment executed successfully.');

            $user = $this->getUser();

            if (!$user) {
                $this->logger->error('User not found during payment success.');
                return $this->redirectToRoute('app_index');
            }

            $user = $this->userManager->getUserbyEmail($user->getUserIdentifier());
            $order = $this->ordersManager->getCurrentOrder($user, 'summary_for_purchase') ?? new Orders();

            // Recalculate total from the cart
            $cart = $this->cartManager->getCart($user);
            if (is_array($cart)) {
                $cart = $cart[0];
            }

            $allCartItems = $this->cartManager->getAllCartArticle($cart);
            $allArticlesPrices = 0;
            foreach ($allCartItems as $articleItem) {
                $allArticlesPrices += $articleItem['article']->getArticlePrice() * $articleItem['quantity'];
            }

            // Create and save payment record
            $paymentEntity = new PaymentEntity();
            $paymentEntity->setUser($user)
                ->setOrder($order)
                ->setPaymentArt('Paypal')
                ->setAmount($allArticlesPrices);

            $this->entityManager->persist($paymentEntity);
            $order->setNextTransition('proceed_to_send_mail');
            $this->dispatcher->dispatch(new OrderEvent($order), OrderEvent::NAME);
            $this->entityManager->flush();


            $this->logger->info("Payment and order successfully recorded.");

            // Redirect to checkout or order confirmation
            return $this->redirectToRoute('checkout');
        } catch (\Exception $e) {
            $this->logger->error('Payment execution failed: ' . $e->getMessage());
            return $this->redirectToRoute('payment_cancel');
        }
    }

    #[Route("/payment-cancel", name: 'payment_cancel')]
    public function paymentCancel(): Response
    {
        return $this->render('payment-cancel.html.twig');
    }
}
