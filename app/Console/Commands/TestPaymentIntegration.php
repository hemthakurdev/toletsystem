<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentService;
use App\Models\Invoice;
use App\Models\Organization;

class TestPaymentIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:test {--invoice-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Razorpay payment integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Razorpay Payment Integration...');
        $this->newLine();

        // Test 1: Check Razorpay Configuration
        $this->info('1. Checking Razorpay Configuration...');
        $paymentService = new PaymentService();
        
        if (!$paymentService->isConfigured()) {
            $this->error('❌ Razorpay is not configured!');
            $this->warn('Please set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in your .env file');
            return 1;
        }
        
        $this->info('✅ Razorpay configuration is valid');
        $this->newLine();

        // Test 2: Get Frontend Config
        $this->info('2. Testing Frontend Configuration...');
        $config = $paymentService->getFrontendConfig();
        
        if (empty($config['key_id'])) {
            $this->error('❌ Frontend configuration failed');
            return 1;
        }
        
        $this->info('✅ Frontend configuration is ready');
        $this->info("   Key ID: {$config['key_id']}");
        $this->newLine();

        // Test 3: Test with Sample Invoice
        $this->info('3. Testing Payment Order Creation...');
        
        $invoiceId = $this->option('invoice-id');
        if (!$invoiceId) {
            // Get first unpaid invoice
            $invoice = Invoice::where('status', '!=', 'paid')->first();
            if (!$invoice) {
                $this->warn('⚠️  No unpaid invoices found. Creating a test invoice...');
                $invoice = $this->createTestInvoice();
            }
        } else {
            $invoice = Invoice::find($invoiceId);
            if (!$invoice) {
                $this->error("❌ Invoice with ID {$invoiceId} not found");
                return 1;
            }
        }

        $this->info("   Testing with Invoice #{$invoice->invoice_no} (Amount: ₹{$invoice->total_amount})");

        // Test payment order creation
        $result = $paymentService->processInvoicePayment($invoice, []);
        
        if ($result['success']) {
            $this->info('✅ Payment order created successfully');
            $this->info("   Order ID: {$result['order_id']}");
            $this->info("   Amount: ₹" . ($result['amount'] / 100));
        } else {
            $this->error('❌ Payment order creation failed');
            $this->error("   Error: {$result['error']}");
            return 1;
        }

        $this->newLine();

        // Test 4: Payment Statistics
        $this->info('4. Testing Payment Statistics...');
        $orgId = $invoice->org_id;
        $stats = $paymentService->getPaymentStatistics($orgId, 'month');
        
        $this->info('✅ Payment statistics retrieved');
        $this->info("   Total Payments: {$stats['total_payments']}");
        $this->info("   Total Amount: ₹{$stats['total_amount']}");
        $this->info("   Success Rate: " . number_format($stats['success_rate'], 2) . "%");

        $this->newLine();
        $this->info('🎉 All payment integration tests passed!');
        $this->newLine();
        
        $this->warn('📝 Next Steps:');
        $this->line('   1. Configure your Razorpay API keys in .env file');
        $this->line('   2. Test with real payment methods');
        $this->line('   3. Set up webhook endpoints');
        $this->line('   4. Deploy to production');

        return 0;
    }

    /**
     * Create a test invoice for testing
     */
    private function createTestInvoice()
    {
        $organization = Organization::first();
        if (!$organization) {
            $this->error('❌ No organization found. Please run database seeders first.');
            return null;
        }

        $invoice = Invoice::create([
            'org_id' => $organization->id,
            'tenant_id' => null,
            'property_id' => null,
            'invoice_no' => 'TEST-' . time(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000.00,
            'tax_amount' => 180.00,
            'total_amount' => 1180.00,
            'status' => 'pending',
            'type' => 'test',
            'description' => 'Test invoice for payment integration',
        ]);

        $this->info("✅ Created test invoice #{$invoice->invoice_no}");
        return $invoice;
    }
}