<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use App\Models\User;
use Illuminate\Console\Command;

class TestEmailSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email? : Email address to send test email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the email system configuration';

    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing SaleMitra Email System...');
        $this->newLine();

        // Check email configuration
        $this->info('📧 Checking email configuration...');
        $mailDriver = config('mail.default');
        $mailHost = config('mail.mailers.smtp.host');
        $mailPort = config('mail.mailers.smtp.port');
        $mailFrom = config('mail.from.address');

        $this->table(
            ['Setting', 'Value'],
            [
                ['Mail Driver', $mailDriver],
                ['SMTP Host', $mailHost],
                ['SMTP Port', $mailPort],
                ['From Address', $mailFrom],
            ]
        );

        $this->newLine();

        // Get test email address
        $testEmail = $this->argument('email') ?? $this->ask('Enter email address to send test email to');

        if (!$testEmail) {
            $this->error('❌ No email address provided. Exiting.');
            return 1;
        }

        // Validate email
        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('❌ Invalid email address format.');
            return 1;
        }

        $this->info("📤 Sending test email to: {$testEmail}");

        // Send test email
        $success = $this->emailService->testEmailConfiguration($testEmail);

        if ($success) {
            $this->info('✅ Test email sent successfully!');
            $this->info('📬 Please check your inbox (and spam folder) for the test email.');
        } else {
            $this->error('❌ Failed to send test email. Check your email configuration.');
            $this->newLine();
            $this->warn('Common issues:');
            $this->line('• SMTP credentials are incorrect');
            $this->line('• SMTP server is not accessible');
            $this->line('• Firewall blocking SMTP port');
            $this->line('• Email service provider blocking the connection');
            return 1;
        }

        $this->newLine();

        // Show email statistics
        $this->info('📊 Email Statistics:');
        $stats = $this->emailService->getEmailStatistics();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Sent Today', $stats['total_sent_today']],
                ['Sent This Week', $stats['total_sent_this_week']],
                ['Sent This Month', $stats['total_sent_this_month']],
                ['Failed Today', $stats['failed_emails_today']],
                ['Most Common Type', $stats['most_common_email_type']],
            ]
        );

        $this->newLine();
        $this->info('🎉 Email system test completed!');

        return 0;
    }
}
