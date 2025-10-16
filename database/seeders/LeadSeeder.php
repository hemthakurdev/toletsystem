<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Organization;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first organization and its properties
        $organization = Organization::first();
        
        if (!$organization) {
            $this->command->info('No organization found. Please run OrganizationSeeder first.');
            return;
        }

        $properties = Property::where('org_id', $organization->id)->get();
        
        if ($properties->isEmpty()) {
            $this->command->info('No properties found. Please run PropertySeeder first.');
            return;
        }

        $sampleLeads = [
            [
                'property_id' => $properties->first()->id,
                'org_id' => $organization->id,
                'name' => 'Rajesh Kumar',
                'phone' => '+91 98765 43210',
                'email' => 'rajesh.kumar@email.com',
                'message' => 'Hi, I am interested in renting this property. I am a software engineer working in Mumbai. Could you please provide more details about the rent and availability?',
                'status' => 'new',
                'source' => 'public_listing',
                'lead_score' => 85,
            ],
            [
                'property_id' => $properties->first()->id,
                'org_id' => $organization->id,
                'name' => 'Priya Sharma',
                'phone' => '+91 87654 32109',
                'email' => 'priya.sharma@email.com',
                'message' => 'Hello, I am looking for a 2BHK apartment in this area. Is this property still available? What is the security deposit amount?',
                'status' => 'contacted',
                'source' => 'public_listing',
                'lead_score' => 75,
                'contacted_at' => now()->subHours(2),
            ],
            [
                'property_id' => $properties->skip(1)->first()?->id ?? $properties->first()->id,
                'org_id' => $organization->id,
                'name' => 'Amit Patel',
                'phone' => '+91 76543 21098',
                'email' => 'amit.patel@email.com',
                'message' => 'I am interested in this property for my family. We are relocating to Mumbai next month. Can we schedule a property visit?',
                'status' => 'interested',
                'source' => 'referral',
                'lead_score' => 90,
                'contacted_at' => now()->subDays(1),
            ],
            [
                'property_id' => $properties->first()->id,
                'org_id' => $organization->id,
                'name' => 'Sneha Gupta',
                'phone' => '+91 65432 10987',
                'email' => 'sneha.gupta@email.com',
                'message' => 'Hi, I am a working professional looking for a furnished apartment. Is this property pet-friendly?',
                'status' => 'new',
                'source' => 'public_listing',
                'lead_score' => 70,
            ],
            [
                'property_id' => $properties->skip(1)->first()?->id ?? $properties->first()->id,
                'org_id' => $organization->id,
                'name' => 'Vikram Singh',
                'phone' => '+91 54321 09876',
                'email' => 'vikram.singh@email.com',
                'message' => 'I am looking for a property in this locality. What are the nearby amenities and transportation options?',
                'status' => 'not_interested',
                'source' => 'public_listing',
                'lead_score' => 45,
                'contacted_at' => now()->subDays(3),
            ],
            [
                'property_id' => $properties->first()->id,
                'org_id' => $organization->id,
                'name' => 'Anita Reddy',
                'phone' => '+91 43210 98765',
                'email' => 'anita.reddy@email.com',
                'message' => 'Hello, I am interested in this property. I am a doctor working in a nearby hospital. Could you please share the lease terms and conditions?',
                'status' => 'converted',
                'source' => 'public_listing',
                'lead_score' => 95,
                'contacted_at' => now()->subDays(5),
            ],
        ];

        foreach ($sampleLeads as $leadData) {
            Lead::create($leadData);
        }

        $this->command->info('Sample leads created successfully!');
    }
}
