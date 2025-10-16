<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first plan (created by PlanSeeder)
        $plan = Plan::first();
        
        if (!$plan) {
            $this->command->info('No plan found. Please run PlanSeeder first.');
            return;
        }

        $organization = Organization::firstOrCreate([
            'email' => 'demo@salemitra.com'
        ], [
            'name' => 'Demo Property Management',
            'phone' => '+91 98765 43210',
            'address' => '123 Business Park, Mumbai, Maharashtra',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        // Create a demo admin user for this organization
        $user = User::firstOrCreate([
            'email' => 'admin@salemitra.com'
        ], [
            'name' => 'Demo Admin',
            'password' => bcrypt('password123'),
            'org_id' => $organization->id,
            'phone' => '+91 98765 43210',
            'email_verified_at' => now(),
        ]);

        // Assign admin role to the user
        $user->assignRole('admin');

        $this->command->info('Organization and admin user created successfully!');
        $this->command->info('Email: admin@salemitra.com');
        $this->command->info('Password: password123');
    }
}