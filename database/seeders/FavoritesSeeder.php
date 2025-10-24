<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Favorite;
use App\Models\User;
use App\Models\Property;

class FavoritesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get frontend users
        $frontendUsers = User::where('user_type', 'frontend')->get();
        
        // Get published properties
        $properties = Property::where('published', true)->get();
        
        if ($frontendUsers->count() > 0 && $properties->count() > 0) {
            // Create some sample favorites
            foreach ($frontendUsers as $user) {
                // Each user favorites 1-3 random properties (or all if less than 3)
                $maxFavorites = min(3, $properties->count());
                $favoriteCount = rand(1, $maxFavorites);
                $randomProperties = $properties->random($favoriteCount);
                
                foreach ($randomProperties as $property) {
                    Favorite::firstOrCreate([
                        'user_id' => $user->id,
                        'property_id' => $property->id,
                    ]);
                }
            }
        }
    }
}