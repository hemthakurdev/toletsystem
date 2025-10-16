<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Lead;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\EmailService;
use App\Mail\WelcomeEmail;
use App\Mail\InvoiceEmail;
use App\Mail\LeadNotificationEmail;
use App\Mail\PaymentConfirmationEmail;
use App\Mail\PropertyApprovalEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class EmailSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;
    protected $property;

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

        // Fake mail and queue
        Mail::fake();
        Queue::fake();
    }

    /** @test */
    public function welcome_email_can_be_sent()
    {
        $emailService = app(EmailService::class);
        
        $emailService->sendWelcomeEmail($this->user, 'password123');
        
        Mail::assertSent(WelcomeEmail::class, function ($mail) {
            return $mail->user->id === $this->user->id;
        });
    }

    /** @test */
    public function invoice_email_can_be_sent()
    {
        $invoice = Invoice::factory()->create([
            'org_id' => $this->organization->id,
            'tenant_id' => $this->user->id,
            'property_id' => $this->property->id,
        ]);

        $emailService = app(EmailService::class);
        
        $emailService->sendInvoiceEmail($invoice, $this->user);
        
        Mail::assertSent(InvoiceEmail::class, function ($mail) use ($invoice) {
            return $mail->invoice->id === $invoice->id && 
                   $mail->user->id === $this->user->id;
        });
    }

    /** @test */
    public function lead_notification_email_can_be_sent()
    {
        $lead = Lead::factory()->create([
            'property_id' => $this->property->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ]);

        $emailService = app(EmailService::class);
        
        $emailService->sendLeadNotificationEmail($lead, $this->property, $this->user);
        
        Mail::assertSent(LeadNotificationEmail::class, function ($mail) use ($lead) {
            return $mail->lead->id === $lead->id && 
                   $mail->property->id === $this->property->id &&
                   $mail->propertyOwner->id === $this->user->id;
        });
    }

    /** @test */
    public function payment_confirmation_email_can_be_sent()
    {
        $invoice = Invoice::factory()->create([
            'org_id' => $this->organization->id,
            'tenant_id' => $this->user->id,
            'property_id' => $this->property->id,
        ]);

        $payment = Payment::factory()->create([
            'org_id' => $this->organization->id,
            'invoice_id' => $invoice->id,
            'amount' => 10000,
            'status' => 'completed',
        ]);

        $emailService = app(EmailService::class);
        
        $emailService->sendPaymentConfirmationEmail($payment, $this->user);
        
        Mail::assertSent(PaymentConfirmationEmail::class, function ($mail) use ($payment) {
            return $mail->payment->id === $payment->id && 
                   $mail->user->id === $this->user->id;
        });
    }

    /** @test */
    public function property_approval_email_can_be_sent()
    {
        $emailService = app(EmailService::class);
        
        $emailService->sendPropertyApprovalEmail($this->property, $this->user, 'approved');
        
        Mail::assertSent(PropertyApprovalEmail::class, function ($mail) {
            return $mail->property->id === $this->property->id && 
                   $mail->user->id === $this->user->id &&
                   $mail->status === 'approved';
        });
    }

    /** @test */
    public function property_rejection_email_can_be_sent()
    {
        $emailService = app(EmailService::class);
        
        $emailService->sendPropertyApprovalEmail($this->property, $this->user, 'rejected', 'Insufficient information');
        
        Mail::assertSent(PropertyApprovalEmail::class, function ($mail) {
            return $mail->property->id === $this->property->id && 
                   $mail->user->id === $this->user->id &&
                   $mail->status === 'rejected' &&
                   $mail->reason === 'Insufficient information';
        });
    }

    /** @test */
    public function email_service_can_test_configuration()
    {
        $emailService = app(EmailService::class);
        
        $result = $emailService->testConfiguration();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    /** @test */
    public function email_service_can_get_statistics()
    {
        $emailService = app(EmailService::class);
        
        $stats = $emailService->getEmailStatistics();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_sent', $stats);
        $this->assertArrayHasKey('total_failed', $stats);
        $this->assertArrayHasKey('email_types', $stats);
    }

    /** @test */
    public function welcome_email_has_correct_content()
    {
        $mail = new WelcomeEmail($this->user, 'password123');
        
        $this->assertEquals('Welcome to SaleMitra - Your Account is Ready!', $mail->envelope()->subject);
        $this->assertStringContainsString($this->user->name, $mail->content()->view);
    }

    /** @test */
    public function invoice_email_has_correct_content()
    {
        $invoice = Invoice::factory()->create([
            'org_id' => $this->organization->id,
            'tenant_id' => $this->user->id,
            'property_id' => $this->property->id,
            'invoice_number' => 'INV-001',
        ]);

        $mail = new InvoiceEmail($invoice, $this->user);
        
        $this->assertEquals("Invoice #INV-001 - SaleMitra", $mail->envelope()->subject);
        $this->assertStringContainsString('INV-001', $mail->content()->view);
    }

    /** @test */
    public function lead_notification_email_has_correct_content()
    {
        $lead = Lead::factory()->create([
            'property_id' => $this->property->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $mail = new LeadNotificationEmail($lead, $this->property, $this->user);
        
        $this->assertEquals("New Lead for {$this->property->title} - SaleMitra", $mail->envelope()->subject);
        $this->assertStringContainsString($lead->name, $mail->content()->view);
    }

    /** @test */
    public function payment_confirmation_email_has_correct_content()
    {
        $invoice = Invoice::factory()->create([
            'org_id' => $this->organization->id,
            'tenant_id' => $this->user->id,
            'property_id' => $this->property->id,
        ]);

        $payment = Payment::factory()->create([
            'org_id' => $this->organization->id,
            'invoice_id' => $invoice->id,
            'amount' => 10000,
            'status' => 'completed',
        ]);

        $mail = new PaymentConfirmationEmail($payment, $this->user);
        
        $this->assertEquals("Payment Confirmation - ₹10,000 - SaleMitra", $mail->envelope()->subject);
        $this->assertStringContainsString('10,000', $mail->content()->view);
    }

    /** @test */
    public function property_approval_email_has_correct_subject_for_approval()
    {
        $mail = new PropertyApprovalEmail($this->property, $this->user, 'approved');
        
        $this->assertEquals("Property Approved: {$this->property->title} - SaleMitra", $mail->envelope()->subject);
    }

    /** @test */
    public function property_approval_email_has_correct_subject_for_rejection()
    {
        $mail = new PropertyApprovalEmail($this->property, $this->user, 'rejected', 'Test reason');
        
        $this->assertEquals("Property Rejected: {$this->property->title} - SaleMitra", $mail->envelope()->subject);
    }

    /** @test */
    public function emails_are_queued_for_background_processing()
    {
        $emailService = app(EmailService::class);
        
        $emailService->sendWelcomeEmail($this->user, 'password123');
        
        Queue::assertPushed(WelcomeEmail::class);
    }

    /** @test */
    public function email_service_handles_missing_configuration_gracefully()
    {
        // Temporarily set invalid mail configuration
        config(['mail.mailers.smtp.host' => '']);
        
        $emailService = app(EmailService::class);
        
        $result = $emailService->testConfiguration();
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /** @test */
    public function bulk_email_sending_works()
    {
        $users = User::factory()->count(3)->create([
            'org_id' => $this->organization->id,
        ]);

        $emailService = app(EmailService::class);
        
        foreach ($users as $user) {
            $emailService->sendWelcomeEmail($user, 'password123');
        }
        
        Mail::assertSent(WelcomeEmail::class, 3);
    }

    /** @test */
    public function email_templates_are_accessible()
    {
        $this->assertFileExists(resource_path('views/emails/welcome.blade.php'));
        $this->assertFileExists(resource_path('views/emails/invoice.blade.php'));
        $this->assertFileExists(resource_path('views/emails/lead-notification.blade.php'));
        $this->assertFileExists(resource_path('views/emails/payment-confirmation.blade.php'));
        $this->assertFileExists(resource_path('views/emails/property-approval.blade.php'));
    }
}