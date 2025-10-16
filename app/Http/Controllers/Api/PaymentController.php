<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of payments
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['invoice', 'tenant', 'property'])
            ->where('org_id', auth()->user()->org_id);

        // Apply filters
        if ($request->has('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('razorpay_payment_id', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function ($tenantQuery) use ($search) {
                      $tenantQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $payments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Create payment for invoice
     */
    public function createPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $invoice = Invoice::findOrFail($request->invoice_id);

        // Check if invoice belongs to user's organization
        if ($invoice->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to process payment for this invoice',
            ], 403);
        }

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid',
            ], 400);
        }

        // Check if Razorpay is configured
        if (!$this->paymentService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway is not configured',
            ], 500);
        }

        $result = $this->paymentService->processInvoicePayment($invoice, $request->all());

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Payment order created successfully',
                'data' => $result,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order: ' . $result['error'],
            ], 500);
        }
    }

    /**
     * Complete payment after Razorpay callback
     */
    public function completePayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|exists:payments,id',
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payment = Payment::findOrFail($request->payment_id);

        // Check if payment belongs to user's organization
        if ($payment->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to complete this payment',
            ], 403);
        }

        $result = $this->paymentService->completePayment($payment, $request->all());

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Payment completed successfully',
                'data' => $result,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Payment completion failed: ' . $result['error'],
            ], 500);
        }
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment): JsonResponse
    {
        // Check if payment belongs to user's organization
        if ($payment->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this payment',
            ], 403);
        }

        $payment->load(['invoice', 'tenant', 'property']);

        return response()->json([
            'success' => true,
            'data' => $payment,
        ]);
    }

    /**
     * Process refund for payment
     */
    public function refund(Request $request, Payment $payment): JsonResponse
    {
        // Check if payment belongs to user's organization
        if ($payment->org_id !== auth()->user()->org_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to refund this payment',
            ], 403);
        }

        // Check if payment is completed
        if ($payment->payment_status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Only completed payments can be refunded',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0.01|max:' . $payment->amount,
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->paymentService->processRefund($payment, $request->amount);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => $result,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Refund processing failed: ' . $result['error'],
            ], 500);
        }
    }

    /**
     * Get payment statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $stats = $this->paymentService->getPaymentStatistics(auth()->user()->org_id, $period);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get Razorpay configuration for frontend
     */
    public function getConfig(): JsonResponse
    {
        if (!$this->paymentService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway is not configured',
            ], 500);
        }

        $config = $this->paymentService->getFrontendConfig();

        return response()->json([
            'success' => true,
            'data' => $config,
        ]);
    }

    /**
     * Webhook handler for Razorpay events
     */
    public function webhook(Request $request): JsonResponse
    {
        $webhookSecret = config('services.razorpay.webhook_secret');
        
        if (!$webhookSecret) {
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        $webhookSignature = $request->header('X-Razorpay-Signature');
        $webhookBody = $request->getContent();

        // Verify webhook signature
        $expectedSignature = hash_hmac('sha256', $webhookBody, $webhookSecret);

        if (!hash_equals($expectedSignature, $webhookSignature)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($webhookBody, true);

        // Handle different webhook events
        switch ($event['event']) {
            case 'payment.captured':
                $this->handlePaymentCaptured($event['payload']['payment']['entity']);
                break;
            case 'payment.failed':
                $this->handlePaymentFailed($event['payload']['payment']['entity']);
                break;
            case 'refund.created':
                $this->handleRefundCreated($event['payload']['refund']['entity']);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle payment captured webhook
     */
    protected function handlePaymentCaptured(array $paymentData): void
    {
        // Find payment by Razorpay payment ID
        $payment = Payment::where('razorpay_payment_id', $paymentData['id'])->first();

        if ($payment && $payment->payment_status === 'pending') {
            $payment->update([
                'payment_status' => 'completed',
                'paid_at' => now(),
                'gateway_response' => json_encode($paymentData),
            ]);

            // Update invoice status
            if ($payment->invoice) {
                $payment->invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
        }
    }

    /**
     * Handle payment failed webhook
     */
    protected function handlePaymentFailed(array $paymentData): void
    {
        $payment = Payment::where('razorpay_payment_id', $paymentData['id'])->first();

        if ($payment) {
            $payment->update([
                'payment_status' => 'failed',
                'gateway_response' => json_encode($paymentData),
            ]);
        }
    }

    /**
     * Handle refund created webhook
     */
    protected function handleRefundCreated(array $refundData): void
    {
        $payment = Payment::where('razorpay_payment_id', $refundData['payment_id'])->first();

        if ($payment) {
            $payment->update([
                'refund_amount' => $refundData['amount'] / 100,
                'refund_id' => $refundData['id'],
                'refund_status' => $refundData['status'],
                'refunded_at' => now(),
            ]);
        }
    }
}
