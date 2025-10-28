<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;
use Exception;

class PaySkyService
{
    protected PaymentGateway $gateway;
    protected string $merchantId;
    protected string $terminalId;
    protected string $secretKey;
    protected string $lightboxUrl;
    protected bool $isTestMode;

    public function __construct()
    {
        $this->gateway = PaymentGateway::getBySlug('paysky');

        if (!$this->gateway || !$this->gateway->isConfigured()) {
            throw new Exception('PaySky gateway is not configured properly');
        }

        $this->isTestMode = $this->gateway->is_test_mode;

        // Use test or live credentials based on mode
        if ($this->isTestMode) {
            $this->merchantId = $this->gateway->getCredential('test_merchant_id') ?? $this->gateway->getCredential('merchant_id');
            $this->terminalId = $this->gateway->getCredential('test_terminal_id') ?? $this->gateway->getCredential('terminal_id');
            $this->secretKey = $this->gateway->getCredential('test_secret_key') ?? $this->gateway->getCredential('secret_key');
            $this->lightboxUrl = $this->gateway->getConfig('test_lightbox_url', 'https://grey.paysky.io:9006/invchost/JS/LightBox.js');
        } else {
            $this->merchantId = $this->gateway->getCredential('live_merchant_id') ?? $this->gateway->getCredential('merchant_id');
            $this->terminalId = $this->gateway->getCredential('live_terminal_id') ?? $this->gateway->getCredential('terminal_id');
            $this->secretKey = $this->gateway->getCredential('live_secret_key') ?? $this->gateway->getCredential('secret_key');
            $this->lightboxUrl = $this->gateway->getConfig('live_lightbox_url', 'https://cube.paysky.io:6006/js/LightBox.js');
        }
    }

    /**
     * Initialize payment and generate secure hash
     *
     * @param array $paymentData
     * @return array
     */
    public function initializePayment(array $paymentData): array
    {
        // Start detailed logging
        $logId = uniqid('paysky_init_', true);
        Log::channel('paysky')->info("=== PaySky Payment Initialization Started ===", [
            'log_id' => $logId,
            'timestamp' => now()->toIso8601String(),
            'payment_data' => $paymentData,
        ]);

        try {
            // Create transaction record
            Log::channel('paysky')->info("Creating transaction record", ['log_id' => $logId]);

            $transaction = PaymentTransaction::create([
                'payable_id' => $paymentData['payable']->id ?? null,
                'payable_type' => $paymentData['payable'] ? get_class($paymentData['payable']) : null,
                'payment_gateway_id' => $this->gateway->id,
                'user_id' => $paymentData['user_id'] ?? auth()->id(),
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'EGP',
                'description' => $paymentData['description'] ?? 'Payment',
                'customer_data' => $paymentData['customer'] ?? null,
                'status' => 'pending',
            ]);

            // Get current GMT time
            $trxDateTime = gmdate('D, d M Y H:i:s') . ' GMT';

            // Convert amount to smallest currency unit (piasters for EGP)
            // 1 EGP = 100 piasters, so multiply by 100
            $amountInPiasters = (int)($transaction->amount * 100);

            // Generate secure hash
            $secureHash = $this->generateSecureHash(
                $trxDateTime,
                $amountInPiasters,
                $transaction->transaction_id,
                $this->merchantId,
                $this->terminalId,
                $this->secretKey
            );

            // Store request data
            $requestData = [
                'MID' => $this->merchantId,
                'TID' => $this->terminalId,
                'AmountTrxn' => $amountInPiasters,
                'MerchantReference' => $transaction->transaction_id,
                'TrxDateTime' => $trxDateTime,
                'SecureHash' => $secureHash,
            ];

            $transaction->update([
                'request_data' => $requestData,
            ]);

            Log::channel('paysky')->info('PaySky Payment Initialized Successfully', [
                'log_id' => $logId,
                'transaction_id' => $transaction->transaction_id,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'secure_hash' => $secureHash,
                'merchant_id' => $this->merchantId,
                'terminal_id' => $this->terminalId,
            ]);

            return [
                'success' => true,
                'transaction_id' => $transaction->transaction_id,
                'config' => [
                    'MID' => $this->merchantId,
                    'TID' => $this->terminalId,
                    'AmountTrxn' => (string) $amountInPiasters,
                    'SecureHash' => $secureHash,
                    'MerchantReference' => $transaction->transaction_id,
                    'TrxDateTime' => $trxDateTime,
                ],
                'lightbox_url' => $this->lightboxUrl,
                'transaction' => $transaction,
            ];
        } catch (Exception $e) {
            Log::channel('paysky')->error('PaySky Initialization Error', [
                'log_id' => $logId ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate HMAC SHA-256 secure hash
     *
     * @param string $dateTime
     * @param float $amount
     * @param string $merchantReference
     * @param string $merchantId
     * @param string $terminalId
     * @param string $secretKey
     * @return string
     */
    protected function generateSecureHash(
        string $dateTime,
        float $amount,
        string $merchantReference,
        string $merchantId,
        string $terminalId,
        string $secretKey
    ): string {
        // Build the hashing string
        $hashString = sprintf(
            'Amount=%s&DateTimeLocalTrxn=%s&MerchantId=%s&MerchantReference=%s&TerminalId=%s',
            $amount,
            $dateTime,
            $merchantId,
            $merchantReference,
            $terminalId
        );

        // Generate HMAC SHA-256
        $hash = hash_hmac('sha256', $hashString, hex2bin($secretKey), true);

        // Convert to uppercase hex
        return strtoupper(bin2hex($hash));
    }

    /**
     * Process callback from PaySky (Notification Services)
     *
     * @param array $callbackData
     * @return array
     */
    public function processCallback(array $callbackData): array
    {
        $logId = uniqid('paysky_callback_', true);

        try {
            Log::channel('paysky')->info('=== PaySky Notification Received ===', [
                'log_id' => $logId,
                'timestamp' => now()->toIso8601String(),
                'data' => $callbackData,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Validate required fields according to PaySky Notification Services documentation
            $merchantId = $callbackData['MerchantId'] ?? null;
            $terminalId = $callbackData['TerminalId'] ?? null;
            $merchantReference = $callbackData['MerchantReference'] ?? null;
            $secureHash = $callbackData['SecureHash'] ?? null;
            $dateTimeLocalTrxn = $callbackData['DateTimeLocalTrxn'] ?? null;
            $amount = $callbackData['Amount'] ?? null;
            $currency = $callbackData['Currency'] ?? null;
            $actionCode = $callbackData['ActionCode'] ?? null;

            // Verify required fields
            if (!$merchantReference || !$secureHash) {
                Log::error('PaySky Notification Missing Required Fields', [
                    'log_id' => $logId,
                    'data' => $callbackData,
                ]);

                return [
                    'success' => false,
                    'message' => 'Missing required fields',
                ];
            }

            // Verify Merchant ID and Terminal ID
            if ($merchantId !== $this->merchantId || $terminalId !== $this->terminalId) {
                Log::error('PaySky Notification Invalid Merchant/Terminal', [
                    'log_id' => $logId,
                    'expected_merchant' => $this->merchantId,
                    'received_merchant' => $merchantId,
                    'expected_terminal' => $this->terminalId,
                    'received_terminal' => $terminalId,
                ]);

                return [
                    'success' => false,
                    'message' => 'Invalid merchant or terminal',
                ];
            }

            // Verify SecureHash for security
            $isHashValid = $this->verifyNotificationHash($callbackData, $secureHash);

            if (!$isHashValid) {
                Log::error('PaySky Notification Invalid SecureHash', [
                    'log_id' => $logId,
                    'received_hash' => $secureHash,
                    'merchant_reference' => $merchantReference,
                ]);

                return [
                    'success' => false,
                    'message' => 'Invalid secure hash - possible security violation',
                ];
            }

            Log::channel('paysky')->info('PaySky SecureHash Verified Successfully', [
                'log_id' => $logId,
                'merchant_reference' => $merchantReference,
            ]);

            // Find transaction
            $transaction = PaymentTransaction::where('transaction_id', $merchantReference)
                ->forGateway('paysky')
                ->first();

            if (!$transaction) {
                Log::error('PaySky Notification Transaction Not Found', [
                    'log_id' => $logId,
                    'merchant_reference' => $merchantReference,
                ]);

                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                ];
            }

            // Update with callback data
            $transaction->update([
                'callback_data' => $callbackData,
            ]);

            // Get additional fields
            $systemReference = $callbackData['SystemReference'] ?? null;
            $networkReference = $callbackData['NetworkReference'] ?? null;
            $message = $callbackData['Message'] ?? 'Unknown';
            $txnType = $callbackData['TxnType'] ?? null;
            $paidThrough = $callbackData['PaidThrough'] ?? null;
            $payerAccount = $callbackData['PayerAccount'] ?? null;
            $payerName = $callbackData['PayerName'] ?? null;

            // Update gateway transaction ID and payment method
            $updateData = [
                'gateway_transaction_id' => $systemReference,
                'payment_method' => $paidThrough,
            ];

            if ($networkReference) {
                $updateData['callback_data'] = array_merge(
                    $transaction->callback_data ?? [],
                    ['NetworkReference' => $networkReference]
                );
            }

            $transaction->update($updateData);

            // Check if successful (ActionCode '00' means success according to PaySky docs)
            // Transaction Types: 1=Sale, 2=Refund, 3=Void Sale, 4=Void Refund
            if ($actionCode === '00') {
                Log::channel('paysky')->info('PaySky Payment Successful', [
                    'log_id' => $logId,
                    'transaction_id' => $merchantReference,
                    'system_reference' => $systemReference,
                    'amount' => $amount,
                    'currency' => $currency,
                    'paid_through' => $paidThrough,
                    'payer_account' => $payerAccount,
                ]);

                $transaction->markAsSuccess($callbackData);

                return [
                    'success' => true,
                    'message' => 'Payment successful',
                    'transaction' => $transaction,
                ];
            } else {
                Log::channel('paysky')->warning('PaySky Payment Failed', [
                    'log_id' => $logId,
                    'transaction_id' => $merchantReference,
                    'action_code' => $actionCode,
                    'message' => $message,
                ]);

                $failureReason = sprintf(
                    'Payment failed - Code: %s, Message: %s',
                    $actionCode ?? 'N/A',
                    $message
                );

                $transaction->markAsFailed($failureReason, $callbackData);

                return [
                    'success' => false,
                    'message' => $failureReason,
                    'transaction' => $transaction,
                ];
            }
        } catch (Exception $e) {
            Log::channel('paysky')->error('PaySky Notification Processing Error', [
                'log_id' => $logId ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $callbackData ?? [],
            ]);

            return [
                'success' => false,
                'message' => 'Notification processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify SecureHash from PaySky Notification (according to Notification Services documentation)
     *
     * Hash is generated from: Amount, Currency, DateTimeLocalTrxn, MerchantId, TerminalId
     *
     * @param array $callbackData
     * @param string $receivedHash
     * @return bool
     */
    public function verifyNotificationHash(array $callbackData, string $receivedHash): bool
    {
        try {
            // Extract fields according to Notification Services documentation
            $amount = $callbackData['Amount'] ?? '';
            $currency = $callbackData['Currency'] ?? '';
            $dateTimeLocalTrxn = $callbackData['DateTimeLocalTrxn'] ?? '';
            $merchantId = $callbackData['MerchantId'] ?? '';
            $terminalId = $callbackData['TerminalId'] ?? '';

            // Build the hashing string - sorted alphabetically by parameter name
            $hashString = sprintf(
                'Amount=%s&Currency=%s&DateTimeLocalTrxn=%s&MerchantId=%s&TerminalId=%s',
                $amount,
                $currency,
                $dateTimeLocalTrxn,
                $merchantId,
                $terminalId
            );

            Log::channel('paysky')->debug('PaySky Hash Verification', [
                'hash_string' => $hashString,
                'received_hash' => $receivedHash,
            ]);

            // Generate HMAC SHA-256
            $hash = hash_hmac('sha256', $hashString, hex2bin($this->secretKey), true);
            $expectedHash = strtoupper(bin2hex($hash));

            Log::channel('paysky')->debug('PaySky Hash Generated', [
                'expected_hash' => $expectedHash,
            ]);

            // Use hash_equals for timing attack prevention
            return hash_equals($expectedHash, $receivedHash);
        } catch (Exception $e) {
            Log::channel('paysky')->error('PaySky Hash Verification Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return false;
        }
    }

    /**
     * Verify secure hash from callback (for backward compatibility)
     *
     * @param array $callbackData
     * @param string $receivedHash
     * @return bool
     */
    public function verifyCallbackHash(array $callbackData, string $receivedHash): bool
    {
        try {
            $expectedHash = $this->generateSecureHash(
                $callbackData['TrxDateTime'] ?? $callbackData['trxDateTime'] ?? '',
                $callbackData['AmountTrxn'] ?? $callbackData['amountTrxn'] ?? 0,
                $callbackData['MerchantReference'] ?? $callbackData['merchantReference'] ?? '',
                $this->merchantId,
                $this->terminalId,
                $this->secretKey
            );

            return hash_equals($expectedHash, $receivedHash);
        } catch (Exception $e) {
            Log::error('PaySky Hash Verification Error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get gateway information
     *
     * @return array
     */
    public function getGatewayInfo(): array
    {
        return [
            'name' => $this->gateway->name,
            'slug' => $this->gateway->slug,
            'merchant_id' => $this->merchantId,
            'terminal_id' => $this->terminalId,
            'currency' => $this->gateway->currency,
            'is_test_mode' => $this->isTestMode,
            'countries' => $this->gateway->countries,
            'lightbox_url' => $this->lightboxUrl,
        ];
    }

    /**
     * Get test cards
     *
     * @return array
     */
    public static function getTestCards(): array
    {
        return [
            'mastercard' => [
                'number' => '5123 4567 8901 2346',
                'expiry' => '01/32',
                'cvv' => '100',
                'name' => 'Test User',
            ],
            'visa' => [
                'number' => '4440 0000 4220 0014',
                'expiry' => '01/32',
                'cvv' => '100',
                'name' => 'Test User',
            ],
        ];
    }
}
