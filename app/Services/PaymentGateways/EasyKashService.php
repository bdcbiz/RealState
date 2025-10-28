<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EasyKashService
{
    protected PaymentGateway $gateway;
    protected string $apiKey;
    protected string $hmacSecret;
    protected string $callbackUrl;
    protected string $redirectUrl;
    protected bool $isTestMode;

    // EasyKash API endpoints
    protected const API_BASE_URL = 'https://api.easykash.net';
    protected const ENDPOINT_PAY = '/pay';
    protected const ENDPOINT_INQUIRY = '/payment/inquiry';

    public function __construct()
    {
        $this->gateway = PaymentGateway::getBySlug('easykash');

        if (!$this->gateway || !$this->gateway->isConfigured()) {
            throw new Exception('EasyKash gateway is not configured properly');
        }

        $this->apiKey = $this->gateway->getCredential('api_key');
        $this->hmacSecret = $this->gateway->getCredential('hmac_secret');
        $this->callbackUrl = $this->gateway->getCredential('callback_url');
        $this->redirectUrl = $this->gateway->getCredential('redirect_url');
        $this->isTestMode = $this->gateway->is_test_mode;
    }

    /**
     * Create a new payment transaction
     *
     * @param array $paymentData [
     *   'amount' => float,
     *   'currency' => string (default: EGP),
     *   'description' => string,
     *   'customer' => [
     *       'name' => string,
     *       'email' => string,
     *       'phone' => string,
     *   ],
     *   'payable' => Model instance (Order, Invoice, etc),
     *   'user_id' => int (optional),
     *   'metadata' => array (optional),
     * ]
     */
    public function createPayment(array $paymentData): array
    {
        try {
            // Create transaction record
            $transaction = PaymentTransaction::create([
                'payable_id' => $paymentData['payable']->id ?? null,
                'payable_type' => $paymentData['payable'] ? get_class($paymentData['payable']) : null,
                'payment_gateway_id' => $this->gateway->id,
                'user_id' => $paymentData['user_id'] ?? auth()->id(),
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'EGP',
                'description' => $paymentData['description'] ?? 'Payment',
                'customer_data' => $paymentData['customer'] ?? null,
                'callback_url' => $this->callbackUrl,
                'redirect_url' => $this->redirectUrl,
                'status' => 'pending',
            ]);

            // Prepare request payload for EasyKash
            $payload = [
                'amount' => (float) $transaction->amount,
                'currency' => $transaction->currency,
                'merchantTransactionId' => $transaction->transaction_id,
                'description' => $transaction->description,
                'callbackUrl' => $this->callbackUrl,
                'redirectUrl' => $this->redirectUrl,
                'customer' => [
                    'name' => $paymentData['customer']['name'] ?? '',
                    'email' => $paymentData['customer']['email'] ?? '',
                    'phone' => $paymentData['customer']['phone'] ?? '',
                ],
            ];

            // Add metadata if provided
            if (!empty($paymentData['metadata'])) {
                $payload['metadata'] = $paymentData['metadata'];
            }

            Log::info('EasyKash Payment Request', [
                'transaction_id' => $transaction->transaction_id,
                'payload' => $payload,
            ]);

            // Send request to EasyKash API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(self::API_BASE_URL . self::ENDPOINT_PAY, $payload);

            $responseData = $response->json();

            // Update transaction with request/response data
            $transaction->update([
                'request_data' => $payload,
                'response_data' => $responseData,
            ]);

            Log::info('EasyKash Payment Response', [
                'transaction_id' => $transaction->transaction_id,
                'status_code' => $response->status(),
                'response' => $responseData,
            ]);

            if ($response->successful()) {
                // Extract payment URL and transaction reference from response
                $paymentUrl = $responseData['data']['paymentUrl'] ?? null;
                $easykashRef = $responseData['data']['transactionId'] ?? $responseData['data']['reference'] ?? null;

                if ($easykashRef) {
                    $transaction->update([
                        'gateway_transaction_id' => $easykashRef,
                        'status' => 'processing',
                    ]);
                }

                return [
                    'success' => true,
                    'transaction_id' => $transaction->transaction_id,
                    'payment_url' => $paymentUrl,
                    'gateway_reference' => $easykashRef,
                    'transaction' => $transaction,
                ];
            } else {
                $errorMessage = $responseData['message'] ?? 'Payment initiation failed';
                $transaction->markAsFailed($errorMessage, $responseData);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => $responseData,
                    'transaction' => $transaction,
                ];
            }
        } catch (Exception $e) {
            Log::error('EasyKash Payment Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($transaction)) {
                $transaction->markAsFailed($e->getMessage());
            }

            return [
                'success' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify HMAC signature from callback
     */
    public function verifyCallbackSignature(array $callbackData, string $receivedSignature): bool
    {
        try {
            // Sort the data keys alphabetically (as per EasyKash documentation)
            ksort($callbackData);

            // Convert to JSON string
            $dataString = json_encode($callbackData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            // Calculate expected signature
            $expectedSignature = hash_hmac('sha256', $dataString, $this->hmacSecret);

            Log::info('HMAC Verification', [
                'received' => $receivedSignature,
                'expected' => $expectedSignature,
                'data' => $dataString,
            ]);

            // Use hash_equals to prevent timing attacks
            return hash_equals($expectedSignature, $receivedSignature);
        } catch (Exception $e) {
            Log::error('HMAC Verification Failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Process callback from EasyKash
     */
    public function processCallback(array $callbackData): array
    {
        try {
            Log::info('EasyKash Callback Received', ['data' => $callbackData]);

            // Find transaction by merchant transaction ID
            $merchantTransactionId = $callbackData['merchantTransactionId'] ?? null;

            if (!$merchantTransactionId) {
                return [
                    'success' => false,
                    'message' => 'Missing merchantTransactionId in callback',
                ];
            }

            $transaction = PaymentTransaction::where('transaction_id', $merchantTransactionId)
                ->forGateway('easykash')
                ->first();

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                ];
            }

            // Update transaction with callback data
            $transaction->update([
                'callback_data' => $callbackData,
                'gateway_transaction_id' => $callbackData['transactionId'] ?? $transaction->gateway_transaction_id,
                'gateway_reference' => $callbackData['reference'] ?? null,
                'payment_method' => $callbackData['paymentMethod'] ?? null,
            ]);

            // Process based on payment status
            $status = strtolower($callbackData['status'] ?? '');

            switch ($status) {
                case 'success':
                case 'successful':
                case 'completed':
                case 'paid':
                    $transaction->markAsSuccess($callbackData);

                    Log::info('EasyKash Payment Successful', [
                        'transaction_id' => $transaction->transaction_id,
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Payment successful',
                        'transaction' => $transaction,
                    ];

                case 'failed':
                case 'declined':
                case 'rejected':
                    $failureReason = $callbackData['failureReason'] ?? $callbackData['message'] ?? 'Payment failed';
                    $transaction->markAsFailed($failureReason, $callbackData);

                    Log::warning('EasyKash Payment Failed', [
                        'transaction_id' => $transaction->transaction_id,
                        'reason' => $failureReason,
                    ]);

                    return [
                        'success' => false,
                        'message' => $failureReason,
                        'transaction' => $transaction,
                    ];

                case 'pending':
                case 'processing':
                    $transaction->markAsProcessing();

                    return [
                        'success' => true,
                        'message' => 'Payment is processing',
                        'transaction' => $transaction,
                    ];

                case 'cancelled':
                case 'canceled':
                    $transaction->update(['status' => 'cancelled']);

                    return [
                        'success' => false,
                        'message' => 'Payment cancelled',
                        'transaction' => $transaction,
                    ];

                default:
                    Log::warning('Unknown payment status from EasyKash', [
                        'status' => $status,
                        'transaction_id' => $transaction->transaction_id,
                    ]);

                    return [
                        'success' => false,
                        'message' => 'Unknown payment status: ' . $status,
                        'transaction' => $transaction,
                    ];
            }
        } catch (Exception $e) {
            Log::error('EasyKash Callback Processing Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Callback processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Inquire payment status from EasyKash
     */
    public function inquirePaymentStatus(string $transactionId): array
    {
        try {
            $transaction = PaymentTransaction::where('transaction_id', $transactionId)
                ->orWhere('gateway_transaction_id', $transactionId)
                ->forGateway('easykash')
                ->firstOrFail();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get(self::API_BASE_URL . self::ENDPOINT_INQUIRY, [
                'transactionId' => $transaction->gateway_transaction_id ?? $transaction->transaction_id,
            ]);

            $responseData = $response->json();

            Log::info('EasyKash Inquiry Response', [
                'transaction_id' => $transaction->transaction_id,
                'response' => $responseData,
            ]);

            if ($response->successful()) {
                // Update transaction based on inquiry result
                $status = strtolower($responseData['data']['status'] ?? '');

                if ($status === 'success' || $status === 'successful') {
                    $transaction->markAsSuccess($responseData['data']);
                } elseif ($status === 'failed') {
                    $transaction->markAsFailed(
                        $responseData['data']['failureReason'] ?? 'Payment failed',
                        $responseData['data']
                    );
                }

                return [
                    'success' => true,
                    'status' => $status,
                    'data' => $responseData['data'],
                    'transaction' => $transaction,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Inquiry failed',
                    'error' => $responseData,
                ];
            }
        } catch (Exception $e) {
            Log::error('EasyKash Inquiry Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Inquiry failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get transaction by ID
     */
    public function getTransaction(string $transactionId): ?PaymentTransaction
    {
        return PaymentTransaction::where('transaction_id', $transactionId)
            ->orWhere('gateway_transaction_id', $transactionId)
            ->forGateway('easykash')
            ->first();
    }

    /**
     * Get gateway information
     */
    public function getGatewayInfo(): array
    {
        return [
            'name' => $this->gateway->name,
            'slug' => $this->gateway->slug,
            'currency' => $this->gateway->currency,
            'is_test_mode' => $this->isTestMode,
            'countries' => $this->gateway->countries,
        ];
    }
}
