<?php

namespace okpt\furnics\project\Services\Paypal;

use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Psr\Log\LoggerInterface;

class PayPalService
{
    private ApiContext $apiContext;
    private $logger;

    private $clientId = 'AYutUpGlle1wp3YMkeDKJ432K5TWHEikTNBvFKr_IeUCIgQzI1MKyFEI7mvZFWc4c87ZXY1we-4pKCi5'; //'your-paypal-client-id'
    private $secret = 'EJJ6BdLSGq5LW5VXFWKLVnnWer1Wise6Q5fX8HN3geG6WT8bcM_X2dKCYwPv390wHc_yyGkbXd_yeh9z'; //'your-paypal-secret'
    private $mode = 'sandbox'; // or 'live'

    public function __construct(LoggerInterface $log)
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($this->clientId, $this->secret)
        );
        $this->apiContext->setConfig(['mode' => $this->mode]);
        $this->logger = $log;
    }

    public function createPayment(float $total, string $currency, string $paymentDescription, string $successUrl, string $cancelUrl): Payment
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal(number_format($total, 2, '.', ''))
            ->setCurrency($currency);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setDescription($paymentDescription);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($successUrl)
            ->setCancelUrl($cancelUrl);

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions([$transaction])
            ->setRedirectUrls($redirectUrls);

        // Log important details
        $this->logger->info('Creating PayPal payment', [
            'method' => $payer->getPaymentMethod(),
            'amount' => $amount->getTotal(),
            'currency' => $amount->getCurrency(),
            'description' => $paymentDescription,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl
        ]);

        try {
            print_r($payment);
            // Log the data before calling the PayPal SDK
            $this->logger->info('Payment object before creating PayPal payment', [
                'payment' => $payment->toArray()
            ]);

            print_r($this->apiContext);
            // Create the payment
            $payment->create($this->apiContext);
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            $this->logger->error('PayPal Connection Exception: ' . $ex->getMessage());
            $this->logger->error('PayPal Response Data: ' . $ex->getData());
            throw new \Exception("Payment creation failed: " . $ex->getMessage());
        } catch (\Exception $ex) {
            $this->logger->error('Payment creation failed: ' . $ex->getMessage());
            throw new \Exception("Payment creation failed: " . $ex->getMessage());
        }

        return $payment;
    }

    public function executePayment(string $paymentId, string $payerId): Payment
    {
        try {
            $payment = Payment::get($paymentId, $this->apiContext);
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);
            return $payment->execute($execution, $this->apiContext);
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            $this->logger->error('PayPal Connection Exception during execution: ' . $ex->getMessage());
            $this->logger->error('PayPal Response Data: ' . $ex->getData());
            throw new \Exception("Payment execution failed: " . $ex->getMessage());
        } catch (\Exception $ex) {
            $this->logger->error('Payment execution failed: ' . $ex->getMessage());
            throw new \Exception("Payment execution failed: " . $ex->getMessage());
        }
    }
}
