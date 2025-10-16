<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Organization;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first organization
        $organization = Organization::first();
        
        if (!$organization) {
            $this->command->info('No organization found. Please run OrganizationSeeder first.');
            return;
        }

        // Get tenants for this organization
        $tenants = Tenant::where('org_id', $organization->id)->get();
        
        if ($tenants->isEmpty()) {
            $this->command->info('No tenants found. Please run TenantSeeder first.');
            return;
        }

        $invoices = [];

        foreach ($tenants as $tenant) {
            // Create rent invoices for the last 3 months
            for ($i = 2; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $dueDate = $month->copy()->addDays(5); // Due 5 days after month start
                
                $invoices[] = [
                    'org_id' => $organization->id,
                    'tenant_id' => $tenant->id,
                    'property_id' => $tenant->property_id,
                    'invoice_no' => $this->generateInvoiceNumber($organization->id, $month),
                    'invoice_date' => $month->format('Y-m-d'),
                    'due_date' => $dueDate->format('Y-m-d'),
                    'amount' => $tenant->rent_amount,
                    'tax' => 0,
                    'total_amount' => $tenant->rent_amount,
                    'status' => $this->getRandomStatus($i),
                    'notes' => "Monthly rent for {$month->format('F Y')}",
                    'created_at' => $month->format('Y-m-d H:i:s'),
                    'updated_at' => $month->format('Y-m-d H:i:s'),
                ];
            }

            // Create some maintenance invoices
            if (rand(0, 1)) {
                $maintenanceAmount = rand(500, 2000);
                $invoices[] = [
                    'org_id' => $organization->id,
                    'tenant_id' => $tenant->id,
                    'property_id' => $tenant->property_id,
                    'invoice_no' => $this->generateInvoiceNumber($organization->id, now()->subDays(rand(1, 30))),
                    'invoice_date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                    'due_date' => now()->addDays(rand(1, 15))->format('Y-m-d'),
                    'amount' => $maintenanceAmount,
                    'tax' => 0,
                    'total_amount' => $maintenanceAmount,
                    'status' => 'sent',
                    'notes' => 'Maintenance charges for common area cleaning',
                    'created_at' => now()->subDays(rand(1, 30))->format('Y-m-d H:i:s'),
                    'updated_at' => now()->subDays(rand(1, 30))->format('Y-m-d H:i:s'),
                ];
            }

            // Create penalty invoices for some tenants
            if (rand(0, 2) === 0) {
                $penaltyAmount = rand(100, 500);
                $invoices[] = [
                    'org_id' => $organization->id,
                    'tenant_id' => $tenant->id,
                    'property_id' => $tenant->property_id,
                    'invoice_no' => $this->generateInvoiceNumber($organization->id, now()->subDays(rand(1, 15))),
                    'invoice_date' => now()->subDays(rand(1, 15))->format('Y-m-d'),
                    'due_date' => now()->addDays(rand(1, 10))->format('Y-m-d'),
                    'amount' => $penaltyAmount,
                    'tax' => 0,
                    'total_amount' => $penaltyAmount,
                    'status' => 'sent',
                    'notes' => 'Late payment penalty',
                    'created_at' => now()->subDays(rand(1, 15))->format('Y-m-d H:i:s'),
                    'updated_at' => now()->subDays(rand(1, 15))->format('Y-m-d H:i:s'),
                ];
            }
        }

        foreach ($invoices as $invoiceData) {
            Invoice::create($invoiceData);
        }

        $this->command->info('Invoices seeded successfully!');
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber($orgId, $date): string
    {
        $prefix = 'INV';
        $year = $date->format('Y');
        $month = $date->format('m');
        
        // Get a random number for this month
        $number = rand(1, 9999);
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $number);
    }

    /**
     * Get random status based on how old the invoice is
     */
    private function getRandomStatus($monthsAgo): string
    {
        if ($monthsAgo === 2) {
            // 2 months ago - likely paid
            return rand(0, 1) ? 'paid' : 'overdue';
        } elseif ($monthsAgo === 1) {
            // 1 month ago - mixed status
            $rand = rand(0, 2);
            return $rand === 0 ? 'paid' : ($rand === 1 ? 'sent' : 'overdue');
        } else {
            // Current month - likely sent
            return rand(0, 1) ? 'sent' : 'paid';
        }
    }
}