<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;
    protected $property;
    protected $tenant;
    protected $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test organization
        $this->organization = Organization::factory()->create();
        
        // Create test user
        $this->user = User::factory()->create([
            'org_id' => $this->organization->id,
            'email_verified_at' => now(),
        ]);
        
        // Create test property
        $this->property = Property::factory()->create([
            'org_id' => $this->organization->id,
        ]);

        // Create test tenant
        $this->tenant = Tenant::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
        ]);

        // Create test invoice
        $this->invoice = Invoice::factory()->create([
            'org_id' => $this->organization->id,
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'amount' => 10000,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function user_can_create_payment_order()
    {
        $paymentData = [
            'invoice_id' => $this->invoice->id,
            'amount' => 10000,
            'currency' => 'INR',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/org/payments/create-order', $paymentData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'order_id',
                    'amount',
                    'currency',
                    'razorpay_order_id',
                    'key_id'
                ]
            ]);
    }

    /** @test */
    public function payment_order_creation_requires_valid_data()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/org/payments/create-order', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invoice_id', 'amount']);
    }

    /** @test */
    public function user_can_complete_payment()
    {
        // First create a payment order
        $orderResponse = $this->actingAs($this->user)
            ->postJson('/api/v1/org/payments/create-order', [
                'invoice_id' => $this->invoice->id,
                'amount' => 10000,
                'currency' => 'INR',
            ]);

        $orderData = $orderResponse->json('data');

        // Mock successful payment completion
        $paymentData = [
            'razorpay_order_id' => $orderData['razorpay_order_id'],
            'razorpay_payment_id' => 'pay_test123',
            'razorpay_signature' => 'test_signature',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/org/payments/complete', $paymentData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'payment_id',
                    'status',
                    'amount',
                    'invoice'
                ]
            ]);
    }

    /** @test */
    public function payment_completion_requires_valid_signature()
    {
        $paymentData = [
            'razorpay_order_id' => 'order_test123',
            'razorpay_payment_id' => 'pay_test123',
            'razorpay_signature' => 'invalid_signature',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/org/payments/complete', $paymentData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Payment verification failed'
            ]);
    }

    /** @test */
    public function user_can_view_payment_history()
    {
        // Create test payments
        Payment::factory()->count(3)->create([
            'org_id' => $this->organization->id,
            'invoice_id' => $this->invoice->id,
            'amount' => 10000,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/org/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'amount',
                            'status',
                            'payment_method',
                            'created_at',
                            'invoice'
                        ]
                    ],
                    'meta'
                ]
            ]);
    }

    /** @test */
    public function user_can_get_payment_statistics()
    {
        Payment::factory()->create([
            'org_id' => $this->organization->id,
            'invoice_id' => $this->invoice->id,
            'amount' => 10000,
            'status' => 'completed',
        ]);

        Payment::factory()->create([
            'org_id' => $this->organization->id,
            'invoice_id' => $this->invoice->id,
            'amount' => 5000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/org/payments/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_payments',
                    'total_amount',
                    'successful_amount',
                    'pending_amount',
                    'failed_amount',
                    'monthly_trends',
                    'payment_methods'
                ]
            ]);
    }

    /** @test */
    public function admin_can_process_refund()
    {
        $payment = Payment::factory()->create([
            'org_id' => $this->organization->id,
            'invoice_id' => $this->invoice->id,
            'amount' => 10000,
            'status' => 'completed',
            'razorpay_payment_id' => 'pay_test123',
        ]);

        $refundData = [
            'amount' => 5000,
            'reason' => 'Partial refund requested',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/org/payments/{$payment->id}/refund", $refundData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Refund processed successfully'
            ]);
    }

    /** @test */
    public function refund_amount_cannot_exceed_payment_amount()
    {
        $payment = Payment::factory()->create([
            'org_id' => $this->organization->id,
            'invoice_id' => $this->invoice->id,
            'amount' => 10000,
            'status' => 'completed',
        ]);

        $refundData = [
            'amount' => 15000, // More than payment amount
            'reason' => 'Invalid refund amount',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/org/payments/{$payment->id}/refund", $refundData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function payment_service_can_check_configuration()
    {
        $paymentService = app(PaymentService::class);
        
        // This should return false since we're using test keys
        $isConfigured = $paymentService->isConfigured();
        
        $this->assertIsBool($isConfigured);
    }

    /** @test */
    public function payment_service_can_get_frontend_config()
    {
        $paymentService = app(PaymentService::class);
        $config = $paymentService->getFrontendConfig();
        
        $this->assertArrayHasKey('key_id', $config);
        $this->assertArrayHasKey('currency', $config);
    }

    /** @test */
    public function webhook_can_handle_payment_success()
    {
        $webhookData = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_test123',
                        'amount' => 10000,
                        'currency' => 'INR',
                        'status' => 'captured',
                        'order_id' => 'order_test123',
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/webhooks/razorpay', $webhookData);

        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_validates_signature()
    {
        $webhookData = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_test123',
                        'amount' => 10000,
                        'currency' => 'INR',
                        'status' => 'captured',
                    ]
                ]
            ]
        ];

        // Test without proper signature
        $response = $this->postJson('/api/v1/webhooks/razorpay', $webhookData, [
            'X-Razorpay-Signature' => 'invalid_signature'
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_payment_order()
    {
        $response = $this->postJson('/api/v1/org/payments/create-order', [
            'invoice_id' => $this->invoice->id,
            'amount' => 10000,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function user_cannot_pay_other_organization_invoice()
    {
        $otherOrg = Organization::factory()->create();
        $otherUser = User::factory()->create(['org_id' => $otherOrg->id]);
        $otherInvoice = Invoice::factory()->create(['org_id' => $otherOrg->id]);

        $response = $this->actingAs($otherUser)
            ->postJson('/api/v1/org/payments/create-order', [
                'invoice_id' => $this->invoice->id,
                'amount' => 10000,
            ]);

        $response->assertStatus(403);
    }
}