<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition()
    {
        return [
            'org_id' => Organization::factory(),
            'title' => $this->faker->sentence(3),
            'short_description' => $this->faker->sentence(10),
            'long_description' => $this->faker->paragraph,
            'property_type' => $this->faker->randomElement(['apartment', 'house', 'villa', 'commercial']),
            'category' => $this->faker->randomElement(['rent', 'sale']),
            'price' => $this->faker->numberBetween(5000, 100000),
            'bedrooms' => $this->faker->numberBetween(1, 5),
            'bathrooms' => $this->faker->numberBetween(1, 4),
            'area_sqft' => $this->faker->numberBetween(500, 5000),
            'furnished_status' => $this->faker->randomElement(['furnished', 'semi_furnished', 'unfurnished']),
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'pincode' => $this->faker->postcode,
            'locality' => $this->faker->streetName,
            'status' => $this->faker->randomElement(['active', 'inactive', 'pending']),
            'published' => $this->faker->boolean(70),
        ];
    }
}