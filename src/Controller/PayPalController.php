<?php

namespace okpt\furnics\project\Controller;

use Doctrine\ORM\EntityManagerInterface;
use okpt\furnics\project\Entity\Orders;
use okpt\furnics\project\Entity\Payment as PaymentEntity;
use okpt\furnics\project\Form\SummaryType;
use okpt\furnics\project\Services\CartManager;
use okpt\furnics\project\Services\MailService;
use okpt\furnics\project\Services\Paypal\PayPalService;
use okpt\furnics\project\Services\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PayPalController extends AbstractController
{
    private $paypalService;
    private $userManager;
    private $entityManager;
    private $mailService;
    private $cartManager;
    private $logger;

    public function __construct(
        PayPalService $paypalService,
        MailService $mailService,
        EntityManagerInterface $entityManager,
        UserManager $userManager,
        CartManager $cartManager,
        LoggerInterface $logger
    ) {
        $this->paypalService = $paypalService;
        $this->mailService = $mailService;
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->cartManager = $cartManager;
        $this->logger = $logger;
    }

    #[Route('/pay/pal', name: 'app_pay_pal')]
    public function pay(Request $request): Response
    {
        $this->logger->info("Initiating PayPal payment");

        $totalAmount = $request->query->get('total', 0);
        $currency = $request->query->get('currency', 'USD');
        $paymentDescription = $request->query->get('payment_description', 'Payment for Order');

        try {
            // Generate absolute URLs for success and cancel
            $successUrl = $this->generateUrl('payment_success');
            $cancelUrl = $this->generateUrl('payment_cancel');

            $this->logger->debug('$successUrl: '.'http://localhost:8094'. $successUrl);
            $this->logger->debug('$cancelUrl: '.'http://localhost:8094'. $cancelUrl);

            // Create PayPal payment with correct URLs
            $payment = $this->paypalService->createPayment(
                $totalAmount,
                $currency,
                $paymentDescription,
                'http://localhost:8094'. $successUrl,
                'http://localhost:8094'. $cancelUrl
            );

            // Log the entire PayPal payment object for troubleshooting
            $this->logger->info('PayPal Payment created', [
                'payment_id' => $payment->getId(),
                'status' => $payment->getState(),
                'approval_link' => $payment->getApprovalLink()
            ]);

            // Ensure the approval link is generated properly
            $approvalLink = $payment->getApprovalLink();
            if (!$approvalLink) {
                throw new \Exception('Approval link not found in PayPal response.');
            }

            return $this->redirect($approvalLink);
        } catch (\Exception $e) {
            $this->logger->error('PayPal payment initiation failed: ' . $e->getMessage());
            return $this->redirectToRoute('payment_cancel');
        }
    }

    #[Route("/payment-success", name:'payment_success')]
    public function paymentSuccess(Request $request): Response
    {
        $paymentId = $request->query->get('paymentId');
        $payerId = $request->query->get('PayerID');

        if (!$paymentId || !$payerId) {
            $this->logger->error('Invalid payment data: missing paymentId or payerId');
            return $this->redirectToRoute('payment_cancel');
        }

        try {
            $payment = $this->paypalService->executePayment($paymentId, $payerId);
            $this->logger->info('PayPal payment executed successfully.');

            $user = $this->getUser();

            if (!$user) {
                $this->logger->error('User not found during payment success.');
                return $this->redirectToRoute('app_index');
            }

            $user = $this->userManager->getUserbyEmailAndPassWD($user->getUserIdentifier());
            $order = $this->entityManager->getRepository(Orders::class)->findOneBy(['user' => $user]) ?? new Orders();

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
            $this->entityManager->flush();

            $this->logger->info("Payment and order successfully recorded.");

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
