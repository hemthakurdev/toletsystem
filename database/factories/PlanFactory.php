<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 99, 999),
            'billing_cycle' => $this->faker->randomElement(['monthly', 'quarterly', 'yearly']),
            'properties_limit' => $this->faker->numberBetween(5, 100),
            'users_limit' => $this->faker->numberBetween(2, 20),
            'features' => $this->faker->randomElements([
                'basic_analytics',
                'advanced_analytics',
                'priority_support',
                'custom_branding',
                'api_access',
                'bulk_operations',
                'advanced_search',
                'property_comparison',
                'document_management',
                'expense_tracking'
            ], $this->faker->numberBetween(3, 8)),
            'is_active' => $this->faker->boolean(80),
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function basic()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Basic Plan',
                'price' => 99.00,
                'billing_cycle' => 'monthly',
                'properties_limit' => 5,
                'users_limit' => 2,
                'features' => ['basic_analytics', 'priority_support'],
                'is_active' => true,
                'sort_order' => 1,
            ];
        });
    }

    public function premium()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Premium Plan',
                'price' => 299.00,
                'billing_cycle' => 'monthly',
                'properties_limit' => 25,
                'users_limit' => 5,
                'features' => ['basic_analytics', 'advanced_analytics', 'priority_support', 'bulk_operations'],
                'is_active' => true,
                'sort_order' => 2,
            ];
        });
    }

    public function enterprise()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Enterprise Plan',
                'price' => 599.00,
                'billing_cycle' => 'monthly',
                'properties_limit' => 100,
                'users_limit' => 20,
                'features' => [
                    'basic_analytics',
                    'advanced_analytics',
                    'priority_support',
                    'custom_branding',
                    'api_access',
                    'bulk_operations',
                    'advanced_search',
                    'property_comparison',
                    'document_management',
                    'expense_tracking'
                ],
                'is_active' => true,
                'sort_order' => 3,
            ];
        });
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}