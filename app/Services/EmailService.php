<?php

namespace App\Services;

use App\Mail\WelcomeEmail;
use App\Mail\InvoiceEmail;
use App\Mail\LeadNotificationEmail;
use App\Mail\PaymentConfirmationEmail;
use App\Mail\PropertyApprovalEmail;
use App\Mail\PasswordResetEmail;
use App\Mail\EmailVerificationEmail;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail(User $user, string $password = null): bool
    {
        try {
            Mail::to($user->email)->send(new WelcomeEmail($user, $password));
            Log::info("Welcome email sent to {$user->email}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send welcome email to {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send invoice email
     */
    public function sendInvoiceEmail(Invoice $invoice, User $user, string $pdfPath = null): bool
    {
        try {
            Mail::to($user->email)->send(new InvoiceEmail($invoice, $user, $pdfPath));
            Log::info("Invoice email sent to {$user->email} for invoice #{$invoice->invoice_number}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send invoice email to {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send lead notification email to property owner
     */
    public function sendLeadNotificationEmail(Lead $lead, Property $property, User $propertyOwner): bool
    {
        try {
            Mail::to($propertyOwner->email)->send(new LeadNotificationEmail($lead, $property, $propertyOwner));
            Log::info("Lead notification email sent to {$propertyOwner->email} for property {$property->title}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send lead notification email to {$propertyOwner->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmationEmail(Payment $payment, User $user): bool
    {
        try {
            Mail::to($user->email)->send(new PaymentConfirmationEmail($payment, $user));
            Log::info("Payment confirmation email sent to {$user->email} for payment #{$payment->id}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send payment confirmation email to {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send property approval/rejection email
     */
    public function sendPropertyApprovalEmail(Property $property, User $user, string $status, string $reason = null): bool
    {
        try {
            Mail::to($user->email)->send(new PropertyApprovalEmail($property, $user, $status, $reason));
            Log::info("Property {$status} email sent to {$user->email} for property {$property->title}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send property {$status} email to {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send bulk emails to multiple users
     */
    public function sendBulkEmails(array $users, string $subject, string $template, array $data = []): array
    {
        $results = [];
        
        foreach ($users as $user) {
            try {
                // This would need to be implemented based on your bulk email requirements
                // For now, we'll just log the attempt
                Log::info("Bulk email would be sent to {$user->email}");
                $results[$user->email] = true;
            } catch (\Exception $e) {
                Log::error("Failed to send bulk email to {$user->email}: " . $e->getMessage());
                $results[$user->email] = false;
            }
        }
        
        return $results;
    }

    /**
     * Test email configuration
     */
    public function testEmailConfiguration(string $testEmail): bool
    {
        try {
            Mail::raw('This is a test email from SaleMitra. If you receive this, your email configuration is working correctly.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('SaleMitra Email Test');
            });
            
            Log::info("Test email sent to {$testEmail}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send test email to {$testEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(User $user, string $resetUrl): bool
    {
        try {
            Mail::to($user->email)->send(new PasswordResetEmail($user, $resetUrl));
            Log::info("Password reset email sent to {$user->email}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send password reset email to {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email verification email
     */
    public function sendEmailVerificationEmail(User $user): bool
    {
        try {
            Mail::to($user->email)->send(new EmailVerificationEmail($user));
            Log::info("Email verification sent to {$user->email}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email verification to {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email statistics
     */
    public function getEmailStatistics(): array
    {
        // This would typically query your email logs or database
        // For now, we'll return mock data
        return [
            'total_sent_today' => 0,
            'total_sent_this_week' => 0,
            'total_sent_this_month' => 0,
            'failed_emails_today' => 0,
            'most_common_email_type' => 'welcome',
        ];
    }
}
