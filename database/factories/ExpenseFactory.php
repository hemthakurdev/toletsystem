<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Organization;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition()
    {
        return [
            'org_id' => Organization::factory(),
            'property_id' => Property::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'category' => $this->faker->randomElement(['maintenance', 'utilities', 'repairs', 'cleaning', 'security', 'other']),
            'expense_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'receipt_path' => $this->faker->optional()->filePath(),
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => null,
            ];
        });
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'approved_by' => 1,
                'approved_at' => now(),
                'rejection_reason' => null,
            ];
        });
    }

    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => $this->faker->sentence,
            ];
        });
    }
}