<?php

namespace App\Services;

use Razorpay\Api\Api;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    protected $razorpay;
    protected $keyId;
    protected $keySecret;

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key_id');
        $this->keySecret = config('services.razorpay.key_secret');
        
        if ($this->keyId && $this->keySecret) {
            $this->razorpay = new Api($this->keyId, $this->keySecret);
        }
    }

    /**
     * Create a Razorpay order
     */
    public function createOrder(array $data): array
    {
        try {
            $orderData = [
                'amount' => $data['amount'] * 100, // Convert to paise
                'currency' => $data['currency'] ?? 'INR',
                'receipt' => $data['receipt'],
                'notes' => $data['notes'] ?? [],
            ];

            $order = $this->razorpay->order->create($orderData);

            return [
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'receipt' => $order['receipt'],
                'status' => $order['status'],
                'created_at' => $order['created_at'],
            ];

        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment signature
     */
    public function verifyPayment(array $data): bool
    {
        try {
            $attributes = [
                'razorpay_order_id' => $data['razorpay_order_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
            ];

            $this->razorpay->utility->verifyPaymentSignature($attributes);
            return true;

        } catch (\Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process payment for invoice
     */
    public function processInvoicePayment(Invoice $invoice, array $paymentData): array
    {
        DB::beginTransaction();

        try {
            // Create Razorpay order
            $orderData = [
                'amount' => $invoice->total_amount,
                'currency' => 'INR',
                'receipt' => 'INV-' . $invoice->id . '-' . time(),
                'notes' => [
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $invoice->tenant_id,
                    'property_id' => $invoice->property_id,
                    'type' => 'invoice_payment',
                ],
            ];

            $order = $this->createOrder($orderData);

            if (!$order['success']) {
                throw new \Exception('Failed to create payment order: ' . $order['error']);
            }

            // Create payment record
            $payment = Payment::create([
                'org_id' => $invoice->org_id,
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'property_id' => $invoice->property_id,
                'amount' => $invoice->total_amount,
                'currency' => 'INR',
                'payment_method' => 'razorpay',
                'payment_status' => 'pending',
                'razorpay_order_id' => $order['order_id'],
                'razorpay_payment_id' => null,
                'razorpay_signature' => null,
                'transaction_id' => null,
                'gateway_response' => json_encode($order),
                'notes' => 'Payment initiated via Razorpay',
            ]);

            DB::commit();

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'order_id' => $order['order_id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'key_id' => $this->keyId,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Complete payment after verification
     */
    public function completePayment(Payment $payment, array $paymentData): array
    {
        DB::beginTransaction();

        try {
            // Verify payment signature
            if (!$this->verifyPayment($paymentData)) {
                throw new \Exception('Payment verification failed');
            }

            // Update payment record
            $payment->update([
                'payment_status' => 'completed',
                'razorpay_payment_id' => $paymentData['razorpay_payment_id'],
                'razorpay_signature' => $paymentData['razorpay_signature'],
                'transaction_id' => $paymentData['razorpay_payment_id'],
                'gateway_response' => json_encode($paymentData),
                'paid_at' => now(),
            ]);

            // Update invoice status
            if ($payment->invoice) {
                $payment->invoice->update([
                    'status' => 'paid',
                    'payment_method' => 'razorpay',
                    'payment_reference' => $paymentData['razorpay_payment_id'],
                    'paid_at' => now(),
                ]);
            }

            // Update tenant's last payment date
            if ($payment->tenant) {
                $payment->tenant->update([
                    'last_payment_date' => now(),
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'amount' => $payment->amount,
                'transaction_id' => $payment->transaction_id,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment completion failed: ' . $e->getMessage());

            // Update payment status to failed
            $payment->update([
                'payment_status' => 'failed',
                'gateway_response' => json_encode($paymentData),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process refund
     */
    public function processRefund(Payment $payment, float $amount = null): array
    {
        try {
            $refundAmount = $amount ? $amount * 100 : $payment->amount * 100; // Convert to paise

            $refund = $this->razorpay->payment->fetch($payment->razorpay_payment_id)
                ->refund([
                    'amount' => $refundAmount,
                    'notes' => [
                        'payment_id' => $payment->id,
                        'invoice_id' => $payment->invoice_id,
                        'reason' => 'Refund requested',
                    ],
                ]);

            // Update payment record
            $payment->update([
                'refund_amount' => $refund['amount'] / 100,
                'refund_id' => $refund['id'],
                'refund_status' => $refund['status'],
                'refunded_at' => now(),
            ]);

            return [
                'success' => true,
                'refund_id' => $refund['id'],
                'amount' => $refund['amount'] / 100,
                'status' => $refund['status'],
            ];

        } catch (\Exception $e) {
            Log::error('Refund processing failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get payment details from Razorpay
     */
    public function getPaymentDetails(string $paymentId): array
    {
        try {
            $payment = $this->razorpay->payment->fetch($paymentId);

            return [
                'success' => true,
                'payment' => [
                    'id' => $payment['id'],
                    'amount' => $payment['amount'] / 100,
                    'currency' => $payment['currency'],
                    'status' => $payment['status'],
                    'method' => $payment['method'],
                    'description' => $payment['description'],
                    'created_at' => $payment['created_at'],
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Failed to fetch payment details: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get organization's payment statistics
     */
    public function getPaymentStatistics(int $orgId, string $period = 'month'): array
    {
        $query = Payment::where('org_id', $orgId)
            ->where('payment_status', 'completed');

        switch ($period) {
            case 'week':
                $query->where('created_at', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', now()->subMonth());
                break;
            case 'year':
                $query->where('created_at', '>=', now()->subYear());
                break;
        }

        $payments = $query->get();

        return [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'average_amount' => $payments->avg('amount'),
            'success_rate' => $payments->where('payment_status', 'completed')->count() / max($payments->count(), 1) * 100,
            'payment_methods' => $payments->groupBy('payment_method')->map->count(),
        ];
    }

    /**
     * Check if Razorpay is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->keyId) && !empty($this->keySecret);
    }

    /**
     * Get Razorpay configuration for frontend
     */
    public function getFrontendConfig(): array
    {
        return [
            'key_id' => $this->keyId,
            'currency' => 'INR',
            'name' => config('app.name'),
            'description' => 'Property Management Payment',
            'prefill' => [
                'name' => '',
                'email' => '',
                'contact' => '',
            ],
            'theme' => [
                'color' => '#3B82F6',
            ],
        ];
    }
}
