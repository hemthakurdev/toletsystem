<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Organization;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition()
    {
        return [
            'org_id' => Organization::factory(),
            'property_id' => Property::factory(),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'emergency_contact' => $this->faker->phoneNumber,
            'move_in_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'move_out_date' => $this->faker->optional(0.3)->dateTimeBetween('now', '+1 year'),
            'rent_amount' => $this->faker->numberBetween(5000, 50000),
            'security_deposit' => $this->faker->numberBetween(10000, 100000),
            'status' => $this->faker->randomElement(['active', 'inactive', 'moved_out']),
        ];
    }
}