<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'description' => 'Perfect for individual property owners',
                'price_monthly' => 999.00,
                'price_yearly' => 9990.00,
                'property_limit' => 5,
                'user_limit' => 2,
                'feature_flags' => [
                    'property_management',
                    'tenant_management',
                    'basic_invoicing',
                    'lead_management',
                    'basic_analytics',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'description' => 'Ideal for small property management companies',
                'price_monthly' => 2999.00,
                'price_yearly' => 29990.00,
                'property_limit' => 25,
                'user_limit' => 10,
                'feature_flags' => [
                    'property_management',
                    'tenant_management',
                    'advanced_invoicing',
                    'lead_management',
                    'expense_tracking',
                    'advanced_analytics',
                    'bulk_operations',
                    'api_access',
                    'custom_branding',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'For large property management companies',
                'price_monthly' => 9999.00,
                'price_yearly' => 99990.00,
                'property_limit' => -1, // Unlimited
                'user_limit' => -1, // Unlimited
                'feature_flags' => [
                    'property_management',
                    'tenant_management',
                    'advanced_invoicing',
                    'lead_management',
                    'expense_tracking',
                    'advanced_analytics',
                    'bulk_operations',
                    'api_access',
                    'custom_branding',
                    'white_label',
                    'custom_integrations',
                    'dedicated_support',
                    'priority_support',
                    'custom_reports',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::create($planData);
        }
    }
}