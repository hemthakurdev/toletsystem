<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition()
    {
        return [
            'property_id' => Property::factory(),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'message' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(['new', 'contacted', 'interested', 'not_interested', 'converted']),
            'source' => $this->faker->randomElement(['website', 'phone', 'email', 'referral', 'walk_in']),
            'budget_min' => $this->faker->optional(0.6)->randomFloat(2, 5000, 20000),
            'budget_max' => $this->faker->optional(0.6)->randomFloat(2, 20000, 100000),
            'move_in_date' => $this->faker->optional(0.5)->dateTimeBetween('now', '+3 months'),
            'notes' => $this->faker->optional(0.3)->paragraph,
            'contacted_at' => $this->faker->optional(0.4)->dateTimeBetween('-1 month', 'now'),
            'converted_at' => null,
        ];
    }

    public function new()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'new',
                'contacted_at' => null,
                'converted_at' => null,
            ];
        });
    }

    public function contacted()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'contacted',
                'contacted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'converted_at' => null,
            ];
        });
    }

    public function interested()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'interested',
                'contacted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'converted_at' => null,
            ];
        });
    }

    public function converted()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'converted',
                'contacted_at' => $this->faker->dateTimeBetween('-2 months', '-1 month'),
                'converted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    public function notInterested()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'not_interested',
                'contacted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'converted_at' => null,
            ];
        });
    }
}