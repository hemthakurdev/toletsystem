<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\Organization;

class TenantSeeder extends Seeder
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

        // Get properties for this organization
        $properties = Property::where('org_id', $organization->id)->get();
        
        if ($properties->isEmpty()) {
            $this->command->info('No properties found. Please run PropertySeeder first.');
            return;
        }

        $tenants = [
            [
                'org_id' => $organization->id,
                'property_id' => $properties[0]->id, // First property
                'name' => 'Rajesh Kumar',
                'email' => 'rajesh.kumar@example.com',
                'phone' => '+91 98765 43210',
                'id_proof_type' => 'aadhar',
                'id_proof_number' => '1234 5678 9012',
                'lease_start' => now()->subMonths(6)->format('Y-m-d'),
                'lease_end' => now()->addMonths(6)->format('Y-m-d'),
                'rent_amount' => 45000,
                'security_deposit' => 90000,
                'notes' => 'Excellent tenant, always pays on time. Very cooperative.',
                'status' => 'active',
            ],
            [
                'org_id' => $organization->id,
                'property_id' => $properties[1]->id ?? $properties[0]->id, // Second property or first if only one
                'name' => 'Priya Sharma',
                'email' => 'priya.sharma@example.com',
                'phone' => '+91 98765 43212',
                'id_proof_type' => 'pan',
                'id_proof_number' => 'ABCDE1234F',
                'lease_start' => now()->subMonths(3)->format('Y-m-d'),
                'lease_end' => now()->addMonths(9)->format('Y-m-d'),
                'rent_amount' => 25000,
                'security_deposit' => 50000,
                'notes' => 'New tenant, moved in recently. Very professional.',
                'status' => 'active',
            ],
            [
                'org_id' => $organization->id,
                'property_id' => $properties[2]->id ?? $properties[0]->id, // Third property or first if less than 3
                'name' => 'Amit Singh',
                'email' => 'amit.singh@example.com',
                'phone' => '+91 98765 43214',
                'id_proof_type' => 'driving_license',
                'id_proof_number' => 'DL123456789',
                'lease_start' => now()->subMonths(12)->format('Y-m-d'),
                'lease_end' => now()->subDays(5)->format('Y-m-d'), // Lease expired 5 days ago
                'rent_amount' => 15000,
                'security_deposit' => 30000,
                'notes' => 'Lease expired, need to follow up for renewal or vacate.',
                'status' => 'active',
            ],
            [
                'org_id' => $organization->id,
                'property_id' => $properties[3]->id ?? $properties[0]->id, // Fourth property or first if less than 4
                'name' => 'Neha Gupta',
                'email' => 'neha.gupta@example.com',
                'phone' => '+91 98765 43216',
                'id_proof_type' => 'passport',
                'id_proof_number' => 'P1234567',
                'lease_start' => now()->subMonths(8)->format('Y-m-d'),
                'lease_end' => now()->addDays(15)->format('Y-m-d'), // Lease expiring in 15 days
                'rent_amount' => 120000,
                'security_deposit' => 240000,
                'notes' => 'Commercial tenant, lease expiring soon. Need to discuss renewal.',
                'status' => 'active',
            ],
            [
                'org_id' => $organization->id,
                'property_id' => $properties[4]->id ?? $properties[0]->id, // Fifth property or first if less than 5
                'name' => 'Sunita Reddy',
                'email' => 'sunita.reddy@example.com',
                'phone' => '+91 98765 43218',
                'id_proof_type' => 'voter_id',
                'id_proof_number' => 'VOTER123456',
                'lease_start' => now()->subMonths(2)->format('Y-m-d'),
                'lease_end' => now()->addMonths(10)->format('Y-m-d'),
                'rent_amount' => 15000,
                'security_deposit' => 30000,
                'notes' => 'PG tenant, very clean and responsible.',
                'status' => 'active',
            ]
        ];

        foreach ($tenants as $tenantData) {
            Tenant::create($tenantData);
        }

        // Update property availability status for occupied properties
        $occupiedPropertyIds = collect($tenants)->pluck('property_id')->unique();
        Property::whereIn('id', $occupiedPropertyIds)
            ->update(['availability_status' => 'occupied']);

        $this->command->info('Tenants seeded successfully!');
    }
}