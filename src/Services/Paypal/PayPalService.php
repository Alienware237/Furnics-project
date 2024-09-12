<?php

namespace okpt\furnics\project\Services\Paypal;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class PayPalService
{
    private $client;
    private $clientId;
    private $secret;
    private $mode;
    private $logger;
    private $baseUrl;

    public function __construct(LoggerInterface $logger)
    {
        $this->clientId = 'your-client-id';
        $this->secret = 'your-secret';
        $this->mode = 'sandbox'; // or 'live'
        $this->logger = $logger;
        $this->baseUrl = $this->mode === 'sandbox' ? 'https://api.sandbox.paypal.com' : 'https://api.paypal.com';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
        ]);
    }

    private function getAccessToken(): string
    {
        try {
            $response = $this->client->post('/v1/oauth2/token', [
                'auth' => [$this->clientId, $this->secret],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (!isset($data['access_token'])) {
                throw new \Exception('Failed to retrieve access token from PayPal.');
            }

            return $data['access_token'];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get access token: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createPayment(float $total, string $currency, string $paymentDescription, string $successUrl, string $cancelUrl): array
    {
        $accessToken = $this->getAccessToken();

        $paymentData = [
            'intent' => 'sale',
            'payer' => [
                'payment_method' => 'paypal'
            ],
            'transactions' => [[
                'amount' => [
                    'total' => number_format($total, 2, '.', ''),
                    'currency' => $currency
                ],
                'description' => $paymentDescription
            ]],
            'redirect_urls' => [
                'return_url' => $successUrl,
                'cancel_url' => $cancelUrl
            ]
        ];

        try {
            $response = $this->client->post('/v1/payments/payment', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $paymentData
            ]);

            $data = json_decode($response->getBody(), true);
            $this->logger->info('PayPal payment created successfully', $data);

            return $data;
        } catch (\Exception $e) {
            $this->logger->error('Payment creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function executePayment(string $paymentId, string $payerId): array
    {
        $accessToken = $this->getAccessToken();

        try {
            $response = $this->client->post("/v1/payments/payment/{$paymentId}/execute", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'payer_id' => $payerId
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $this->logger->info('Payment executed successfully', $data);

            return $data;
        } catch (\Exception $e) {
            $this->logger->error('Payment execution failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
