<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'org_id' => Organization::factory(),
            'property_id' => Property::factory(),
            'tenant_id' => Tenant::factory(),
            'invoice_number' => 'INV-' . $this->faker->unique()->numberBetween(1000, 9999),
            'amount' => $this->faker->numberBetween(5000, 50000),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => $this->faker->randomElement(['pending', 'paid', 'overdue']),
            'description' => $this->faker->sentence,
            'notes' => $this->faker->optional()->paragraph,
        ];
    }
}