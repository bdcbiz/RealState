<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AFSService
{
    protected PaymentGateway $gateway;
    protected string $merchantId;
    protected string $apiPassword;
    protected string $apiUsername;
    protected bool $isTestMode;
    protected string $apiVersion = '73'; // Latest API version

    // AFS MPGS API endpoint
    protected const API_BASE_URL = 'https://afs.gateway.mastercard.com/api/rest';

    public function __construct()
    {
        $this->gateway = PaymentGateway::getBySlug('afs');

        if (!$this->gateway || !$this->gateway->isConfigured()) {
            throw new Exception('AFS gateway is not configured properly');
        }

        $this->merchantId = $this->gateway->getCredential('merchant_id');
        $this->apiPassword = $this->gateway->getCredential('api_password');
        $this->apiUsername = $this->gateway->getCredential('api_username', 'merchant.' . $this->merchantId);
        $this->isTestMode = $this->gateway->is_test_mode;
    }

    /**
     * Create a hosted checkout session
     *
     * @param array $paymentData
     * @return array
     */
    public function createSession(array $paymentData): array
    {
        try {
            // Create transaction record
            $transaction = PaymentTransaction::create([
                'payable_id' => $paymentData['payable']->id ?? null,
                'payable_type' => $paymentData['payable'] ? get_class($paymentData['payable']) : null,
                'payment_gateway_id' => $this->gateway->id,
                'user_id' => $paymentData['user_id'] ?? auth()->id(),
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'USD',
                'description' => $paymentData['description'] ?? 'Payment',
                'customer_data' => $paymentData['customer'] ?? null,
                'status' => 'pending',
            ]);

            // Prepare session request
            $sessionData = [
                'apiOperation' => 'CREATE_CHECKOUT_SESSION',
                'interaction' => [
                    'operation' => 'PURCHASE',
                    'returnUrl' => $paymentData['return_url'] ?? config('app.url') . '/api/payment/afs/return',
                    'cancelUrl' => $paymentData['cancel_url'] ?? config('app.url') . '/api/payment/afs/cancel',
                    'merchant' => [
                        'name' => $this->gateway->name ?? 'Dukani',
                    ],
                ],
                'order' => [
                    'id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                ],
            ];

            // Note: Customer data is not included in session creation
            // as AFS API doesn't accept these parameters at session creation time.
            // Customer data will be collected during the hosted checkout process.

            Log::info('AFS Session Request', [
                'transaction_id' => $transaction->transaction_id,
                'data' => $sessionData,
            ]);

            // Make API request
            $url = $this->getApiUrl("/version/{$this->apiVersion}/merchant/{$this->merchantId}/session");

            $response = Http::withBasicAuth($this->apiUsername, $this->apiPassword)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, $sessionData);

            $responseData = $response->json();

            // Update transaction with request/response
            $transaction->update([
                'request_data' => $sessionData,
                'response_data' => $responseData,
            ]);

            Log::info('AFS Session Response', [
                'transaction_id' => $transaction->transaction_id,
                'status_code' => $response->status(),
                'response' => $responseData,
            ]);

            if ($response->successful() && isset($responseData['session'])) {
                $sessionId = $responseData['session']['id'];
                $sessionVersion = $responseData['session']['version'] ?? null;

                // Update transaction with session info
                $transaction->update([
                    'gateway_transaction_id' => $sessionId,
                    'status' => 'processing',
                ]);

                return [
                    'success' => true,
                    'transaction_id' => $transaction->transaction_id,
                    'session_id' => $sessionId,
                    'session_version' => $sessionVersion,
                    'merchant_id' => $this->merchantId,
                    'checkout_url' => $this->getCheckoutUrl($sessionId),
                    'transaction' => $transaction,
                ];
            } else {
                $errorMessage = $responseData['error']['explanation'] ??
                               $responseData['result'] ??
                               'Session creation failed';

                $transaction->markAsFailed($errorMessage, $responseData);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => $responseData,
                    'transaction' => $transaction,
                ];
            }
        } catch (Exception $e) {
            Log::error('AFS Session Creation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($transaction)) {
                $transaction->markAsFailed($e->getMessage());
            }

            return [
                'success' => false,
                'message' => 'Session creation failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve session/order information
     */
    public function retrieveOrder(string $orderId): array
    {
        try {
            $url = $this->getApiUrl("/version/{$this->apiVersion}/merchant/{$this->merchantId}/order/{$orderId}");

            $response = Http::withBasicAuth($this->apiUsername, $this->apiPassword)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get($url);

            $responseData = $response->json();

            Log::info('AFS Retrieve Order', [
                'order_id' => $orderId,
                'response' => $responseData,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $responseData['error']['explanation'] ?? 'Failed to retrieve order',
                    'error' => $responseData,
                ];
            }
        } catch (Exception $e) {
            Log::error('AFS Retrieve Order Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve order: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process payment result after redirect
     */
    public function processPaymentResult(string $transactionId, array $resultData = []): array
    {
        try {
            $transaction = PaymentTransaction::where('transaction_id', $transactionId)
                ->forGateway('afs')
                ->firstOrFail();

            // Retrieve order details from AFS
            $orderResult = $this->retrieveOrder($transactionId);

            if (!$orderResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to retrieve order details',
                    'transaction' => $transaction,
                ];
            }

            $orderData = $orderResult['data'];

            // Update transaction with order data
            $transaction->update([
                'response_data' => array_merge($transaction->response_data ?? [], $orderData),
            ]);

            // Check payment status
            $status = $orderData['status'] ?? null;
            $result = $orderData['result'] ?? null;

            Log::info('AFS Payment Result', [
                'transaction_id' => $transactionId,
                'status' => $status,
                'result' => $result,
            ]);

            if ($status === 'CAPTURED' || $result === 'SUCCESS') {
                // Extract payment details
                $paymentMethod = $orderData['sourceOfFunds']['type'] ?? null;

                $transaction->update([
                    'payment_method' => $paymentMethod,
                ]);

                $transaction->markAsSuccess($orderData);

                return [
                    'success' => true,
                    'message' => 'Payment successful',
                    'transaction' => $transaction,
                ];
            } elseif ($status === 'FAILED' || $result === 'FAILURE') {
                $failureReason = $orderData['response']['gatewayCode'] ??
                                $orderData['error']['explanation'] ??
                                'Payment failed';

                $transaction->markAsFailed($failureReason, $orderData);

                return [
                    'success' => false,
                    'message' => $failureReason,
                    'transaction' => $transaction,
                ];
            } else {
                // Still pending or processing
                return [
                    'success' => true,
                    'message' => 'Payment is being processed',
                    'status' => $status,
                    'transaction' => $transaction,
                ];
            }
        } catch (Exception $e) {
            Log::error('AFS Process Payment Result Error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process payment result: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get API URL
     */
    protected function getApiUrl(string $path): string
    {
        return self::API_BASE_URL . $path;
    }

    /**
     * Get checkout URL for hosted checkout
     */
    protected function getCheckoutUrl(string $sessionId): string
    {
        return "https://afs.gateway.mastercard.com/checkout/version/{$this->apiVersion}/checkout.js?session.id={$sessionId}";
    }

    /**
     * Get gateway information
     */
    public function getGatewayInfo(): array
    {
        return [
            'name' => $this->gateway->name,
            'slug' => $this->gateway->slug,
            'merchant_id' => $this->merchantId,
            'currency' => $this->gateway->currency,
            'is_test_mode' => $this->isTestMode,
            'countries' => $this->gateway->countries,
        ];
    }

    /**
     * Get test cards
     */
    public static function getTestCards(): array
    {
        return [
            'visa' => [
                'number' => '4508750015741019',
                'expiry' => '01/39', // APPROVED
                'cvv' => '100', // MATCH
                'name' => 'Test User',
            ],
            'mastercard' => [
                'number' => '5123450000000008',
                'expiry' => '01/39', // APPROVED
                'cvv' => '100', // MATCH
                'name' => 'Test User',
            ],
            'amex' => [
                'number' => '373708623186001',
                'expiry' => '01/39', // APPROVED
                'cvv' => '1000', // MATCH (4 digits for Amex)
                'name' => 'Test User',
            ],
            'declined' => [
                'number' => '4012000033330026',
                'expiry' => '05/39', // DECLINED
                'cvv' => '100',
                'name' => 'Test Declined',
            ],
        ];
    }
}
