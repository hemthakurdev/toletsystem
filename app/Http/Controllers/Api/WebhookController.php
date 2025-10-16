<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;

class WebhookController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Handle Razorpay webhooks
     */
    public function handleRazorpayWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            $event = $payload['event'] ?? null;
            $entity = $payload['entity'] ?? null;

            Log::info('Razorpay webhook received', [
                'event' => $event,
                'entity' => $entity,
                'payload' => $payload
            ]);

            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($payload);
                    break;
                case 'payment.failed':
                    $this->handlePaymentFailed($payload);
                    break;
                case 'payment.authorized':
                    $this->handlePaymentAuthorized($payload);
                    break;
                case 'order.paid':
                    $this->handleOrderPaid($payload);
                    break;
                case 'subscription.charged':
                    $this->handleSubscriptionCharged($payload);
                    break;
                case 'subscription.completed':
                    $this->handleSubscriptionCompleted($payload);
                    break;
                case 'subscription.cancelled':
                    $this->handleSubscriptionCancelled($payload);
                    break;
                case 'subscription.paused':
                    $this->handleSubscriptionPaused($payload);
                    break;
                case 'subscription.resumed':
                    $this->handleSubscriptionResumed($payload);
                    break;
                default:
                    Log::info('Unhandled Razorpay webhook event', ['event' => $event]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Razorpay webhook error', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Handle payment captured event
     */
    private function handlePaymentCaptured(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'];
        
        DB::beginTransaction();
        try {
            $payment = Payment::where('razorpay_payment_id', $paymentData['id'])->first();
            
            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'razorpay_order_id' => $paymentData['order_id'],
                    'razorpay_signature' => $paymentData['signature'] ?? null,
                    'completed_at' => now(),
                ]);

                // Update related invoice
                if ($payment->invoice) {
                    $payment->invoice->update(['status' => 'paid']);
                }

                // Send confirmation email
                if ($payment->user) {
                    $this->emailService->sendPaymentConfirmationEmail($payment);
                }

                Log::info('Payment captured successfully', [
                    'payment_id' => $payment->id,
                    'razorpay_payment_id' => $paymentData['id']
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle payment captured', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment failed event
     */
    private function handlePaymentFailed(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'];
        
        DB::beginTransaction();
        try {
            $payment = Payment::where('razorpay_payment_id', $paymentData['id'])->first();
            
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $paymentData['error_description'] ?? 'Payment failed',
                    'failed_at' => now(),
                ]);

                Log::info('Payment failed', [
                    'payment_id' => $payment->id,
                    'razorpay_payment_id' => $paymentData['id'],
                    'reason' => $paymentData['error_description']
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle payment failed', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment authorized event
     */
    private function handlePaymentAuthorized(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'];
        
        DB::beginTransaction();
        try {
            $payment = Payment::where('razorpay_payment_id', $paymentData['id'])->first();
            
            if ($payment) {
                $payment->update([
                    'status' => 'authorized',
                    'authorized_at' => now(),
                ]);

                Log::info('Payment authorized', [
                    'payment_id' => $payment->id,
                    'razorpay_payment_id' => $paymentData['id']
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle payment authorized', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);
            throw $e;
        }
    }

    /**
     * Handle order paid event
     */
    private function handleOrderPaid(array $payload): void
    {
        $orderData = $payload['payload']['order']['entity'];
        
        Log::info('Order paid', [
            'razorpay_order_id' => $orderData['id'],
            'amount' => $orderData['amount']
        ]);
    }

    /**
     * Handle subscription charged event
     */
    private function handleSubscriptionCharged(array $payload): void
    {
        $subscriptionData = $payload['payload']['subscription']['entity'];
        
        DB::beginTransaction();
        try {
            $subscription = Subscription::where('razorpay_subscription_id', $subscriptionData['id'])->first();
            
            if ($subscription) {
                // Create payment record for subscription charge
                Payment::create([
                    'org_id' => $subscription->org_id,
                    'user_id' => $subscription->user_id,
                    'subscription_id' => $subscription->id,
                    'amount' => $subscriptionData['plan']['item']['amount'] / 100, // Convert from paise
                    'currency' => $subscriptionData['plan']['item']['currency'],
                    'status' => 'completed',
                    'razorpay_payment_id' => $subscriptionData['short_url'] ?? null,
                    'razorpay_subscription_id' => $subscriptionData['id'],
                    'payment_method' => 'subscription',
                    'completed_at' => now(),
                ]);

                $subscription->update([
                    'status' => 'active',
                    'current_period_start' => now(),
                    'current_period_end' => now()->addMonth(),
                ]);

                Log::info('Subscription charged', [
                    'subscription_id' => $subscription->id,
                    'razorpay_subscription_id' => $subscriptionData['id']
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle subscription charged', [
                'error' => $e->getMessage(),
                'subscription_data' => $subscriptionData
            ]);
            throw $e;
        }
    }

    /**
     * Handle subscription completed event
     */
    private function handleSubscriptionCompleted(array $payload): void
    {
        $subscriptionData = $payload['payload']['subscription']['entity'];
        
        DB::beginTransaction();
        try {
            $subscription = Subscription::where('razorpay_subscription_id', $subscriptionData['id'])->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => 'completed',
                    'ended_at' => now(),
                ]);

                Log::info('Subscription completed', [
                    'subscription_id' => $subscription->id,
                    'razorpay_subscription_id' => $subscriptionData['id']
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle subscription completed', [
                'error' => $e->getMessage(),
                'subscription_data' => $subscriptionData
            ]);
            throw $e;
        }
    }

    /**
     * Handle subscription cancelled event
     */
    private function handleSubscriptionCancelled(array $payload): void
    {
        $subscriptionData = $payload['payload']['subscription']['entity'];
        
        DB::beginTransaction();
        try {
            $subscription = Subscription::where('razorpay_subscription_id', $subscriptionData['id'])->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

                Log::info('Subscription cancelled', [
                    'subscription_id' => $subscription->id,
                    'razorpay_subscription_id' => $subscriptionData['id']
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle subscription cancelled', [
                'error' => $e->getMessage(),
                'subscription_data' => $subscriptionData
            ]);
            throw $e;
        }
    }

    /**
     * Handle subscription paused event
     */
    private function handleSubscriptionPaused(array $payload): void
    {
        $subscriptionData = $payload['payload']['subscription']['entity'];
        
        DB::beginTransaction();
        try {
            $subscription = Subscription::where('razorpay_subscription_id', $subscriptionData['id'])->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => 'paused',
                    'paused_at' => now(),
                ]);

                Log::info('Subscription paused', [
                    'subscription_id' => $subscription->id,
                    'razorpay_subscription_id' => $subscriptionData['id']
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle subscription paused', [
                'error' => $e->getMessage(),
                'subscription_data' => $subscriptionData
            ]);
            throw $e;
        }
    }

    /**
     * Handle subscription resumed event
     */
    private function handleSubscriptionResumed(array $payload): void
    {
        $subscriptionData = $payload['payload']['subscription']['entity'];
        
        DB::beginTransaction();
        try {
            $subscription = Subscription::where('razorpay_subscription_id', $subscriptionData['id'])->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => 'active',
                    'resumed_at' => now(),
                ]);

                Log::info('Subscription resumed', [
                    'subscription_id' => $subscription->id,
                    'razorpay_subscription_id' => $subscriptionData['id']
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle subscription resumed', [
                'error' => $e->getMessage(),
                'subscription_data' => $subscriptionData
            ]);
            throw $e;
        }
    }

    /**
     * Handle custom webhooks
     */
    public function handleCustomWebhook(Request $request, string $webhookType): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event' => 'required|string',
            'data' => 'required|array',
            'timestamp' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $event = $request->event;
            $data = $request->data;
            $timestamp = $request->timestamp;

            Log::info('Custom webhook received', [
                'webhook_type' => $webhookType,
                'event' => $event,
                'data' => $data,
                'timestamp' => $timestamp
            ]);

            switch ($webhookType) {
                case 'property':
                    $this->handlePropertyWebhook($event, $data);
                    break;
                case 'tenant':
                    $this->handleTenantWebhook($event, $data);
                    break;
                case 'invoice':
                    $this->handleInvoiceWebhook($event, $data);
                    break;
                case 'expense':
                    $this->handleExpenseWebhook($event, $data);
                    break;
                case 'document':
                    $this->handleDocumentWebhook($event, $data);
                    break;
                case 'lead':
                    $this->handleLeadWebhook($event, $data);
                    break;
                default:
                    Log::warning('Unknown webhook type', ['webhook_type' => $webhookType]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Custom webhook error', [
                'webhook_type' => $webhookType,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Handle property webhooks
     */
    private function handlePropertyWebhook(string $event, array $data): void
    {
        switch ($event) {
            case 'property.created':
                Log::info('Property created via webhook', $data);
                break;
            case 'property.updated':
                Log::info('Property updated via webhook', $data);
                break;
            case 'property.deleted':
                Log::info('Property deleted via webhook', $data);
                break;
            case 'property.published':
                Log::info('Property published via webhook', $data);
                break;
            case 'property.unpublished':
                Log::info('Property unpublished via webhook', $data);
                break;
        }
    }

    /**
     * Handle tenant webhooks
     */
    private function handleTenantWebhook(string $event, array $data): void
    {
        switch ($event) {
            case 'tenant.created':
                Log::info('Tenant created via webhook', $data);
                break;
            case 'tenant.updated':
                Log::info('Tenant updated via webhook', $data);
                break;
            case 'tenant.deleted':
                Log::info('Tenant deleted via webhook', $data);
                break;
            case 'tenant.moved_in':
                Log::info('Tenant moved in via webhook', $data);
                break;
            case 'tenant.moved_out':
                Log::info('Tenant moved out via webhook', $data);
                break;
        }
    }

    /**
     * Handle invoice webhooks
     */
    private function handleInvoiceWebhook(string $event, array $data): void
    {
        switch ($event) {
            case 'invoice.created':
                Log::info('Invoice created via webhook', $data);
                break;
            case 'invoice.updated':
                Log::info('Invoice updated via webhook', $data);
                break;
            case 'invoice.paid':
                Log::info('Invoice paid via webhook', $data);
                break;
            case 'invoice.overdue':
                Log::info('Invoice overdue via webhook', $data);
                break;
        }
    }

    /**
     * Handle expense webhooks
     */
    private function handleExpenseWebhook(string $event, array $data): void
    {
        switch ($event) {
            case 'expense.created':
                Log::info('Expense created via webhook', $data);
                break;
            case 'expense.approved':
                Log::info('Expense approved via webhook', $data);
                break;
            case 'expense.rejected':
                Log::info('Expense rejected via webhook', $data);
                break;
        }
    }

    /**
     * Handle document webhooks
     */
    private function handleDocumentWebhook(string $event, array $data): void
    {
        switch ($event) {
            case 'document.uploaded':
                Log::info('Document uploaded via webhook', $data);
                break;
            case 'document.downloaded':
                Log::info('Document downloaded via webhook', $data);
                break;
            case 'document.expired':
                Log::info('Document expired via webhook', $data);
                break;
        }
    }

    /**
     * Handle lead webhooks
     */
    private function handleLeadWebhook(string $event, array $data): void
    {
        switch ($event) {
            case 'lead.created':
                Log::info('Lead created via webhook', $data);
                break;
            case 'lead.contacted':
                Log::info('Lead contacted via webhook', $data);
                break;
            case 'lead.converted':
                Log::info('Lead converted via webhook', $data);
                break;
            case 'lead.not_interested':
                Log::info('Lead not interested via webhook', $data);
                break;
        }
    }

    /**
     * Get webhook statistics
     */
    public function getWebhookStats(): JsonResponse
    {
        try {
            $stats = [
                'total_webhooks_received' => 0, // This would come from webhook logs
                'successful_webhooks' => 0,
                'failed_webhooks' => 0,
                'webhook_types' => [
                    'razorpay' => 0,
                    'property' => 0,
                    'tenant' => 0,
                    'invoice' => 0,
                    'expense' => 0,
                    'document' => 0,
                    'lead' => 0,
                ],
                'recent_webhooks' => [], // This would come from webhook logs
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get webhook statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test webhook endpoint
     */
    public function testWebhook(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'webhook_type' => 'required|string',
            'test_data' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $webhookType = $request->webhook_type;
            $testData = $request->test_data ?? [];

            Log::info('Webhook test', [
                'webhook_type' => $webhookType,
                'test_data' => $testData,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook test successful',
                'data' => [
                    'webhook_type' => $webhookType,
                    'test_data' => $testData,
                    'timestamp' => now(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook test failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}