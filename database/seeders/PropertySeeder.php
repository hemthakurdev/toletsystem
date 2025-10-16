<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\Organization;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first organization (created by OrganizationSeeder)
        $organization = Organization::first();
        
        if (!$organization) {
            $this->command->info('No organization found. Please run OrganizationSeeder first.');
            return;
        }

        $properties = [
            [
                'org_id' => $organization->id,
                'title' => 'Beautiful 2BHK Apartment in Bandra West',
                'short_description' => 'Spacious 2BHK apartment with modern amenities in prime location',
                'long_description' => 'This beautiful 2BHK apartment is located in the heart of Bandra West, Mumbai. The property features modern amenities, excellent connectivity, and is close to schools, hospitals, and shopping centers.',
                'property_type' => 'rent',
                'category' => 'Apartment',
                'price' => 45000,
                'security_deposit' => 90000,
                'city' => 'Mumbai',
                'locality' => 'Bandra West',
                'pincode' => '400050',
                'address_line' => 'Hill Road, Bandra West, Mumbai',
                'latitude' => 19.0544,
                'longitude' => 72.8406,
                'furnished_status' => 'furnished',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area_sqft' => 1200,
                'availability_status' => 'vacant',
                'published' => true,
                'featured' => true,
                'published_at' => now(),
                'amenities' => json_encode(['parking', 'balcony', 'gym', 'swimming_pool', 'security', 'power_backup', 'lift']),
            ],
            [
                'org_id' => $organization->id,
                'title' => 'Luxury 3BHK Villa in Whitefield',
                'short_description' => 'Premium 3BHK villa with private garden and modern facilities',
                'long_description' => 'This luxury 3BHK villa is located in Whitefield, Bangalore. It features a private garden, modern kitchen, spacious bedrooms, and is close to IT parks and international schools.',
                'property_type' => 'sale',
                'category' => 'Villa',
                'price' => 8500000,
                'security_deposit' => 0,
                'city' => 'Bangalore',
                'locality' => 'Whitefield',
                'pincode' => '560066',
                'address_line' => 'ITPL Road, Whitefield, Bangalore',
                'latitude' => 12.9698,
                'longitude' => 77.7500,
                'furnished_status' => 'semi_furnished',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'area_sqft' => 2000,
                'availability_status' => 'vacant',
                'published' => true,
                'featured' => false,
                'published_at' => now(),
                'amenities' => json_encode(['parking', 'balcony', 'garden', 'security', 'power_backup']),
            ],
            [
                'org_id' => $organization->id,
                'title' => 'Cozy 1BHK Studio in Koramangala',
                'short_description' => 'Perfect studio apartment for working professionals',
                'long_description' => 'This cozy 1BHK studio is perfect for working professionals. Located in Koramangala, it offers excellent connectivity to IT parks and has all basic amenities.',
                'property_type' => 'rent',
                'category' => 'Studio',
                'price' => 25000,
                'security_deposit' => 50000,
                'city' => 'Bangalore',
                'locality' => 'Koramangala',
                'pincode' => '560034',
                'address_line' => '5th Block, Koramangala, Bangalore',
                'latitude' => 12.9279,
                'longitude' => 77.6271,
                'furnished_status' => 'furnished',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area_sqft' => 600,
                'availability_status' => 'vacant',
                'published' => true,
                'featured' => false,
                'published_at' => now(),
                'amenities' => json_encode(['balcony', 'security', 'lift']),
            ],
            [
                'org_id' => $organization->id,
                'title' => 'Commercial Office Space in Cyber City',
                'short_description' => 'Premium office space in Gurgaon Cyber City',
                'long_description' => 'This premium commercial office space is located in Cyber City, Gurgaon. It offers modern amenities, excellent connectivity, and is perfect for IT companies and startups.',
                'property_type' => 'commercial',
                'category' => 'Office',
                'price' => 120000,
                'security_deposit' => 240000,
                'city' => 'Gurgaon',
                'locality' => 'Cyber City',
                'pincode' => '122002',
                'address_line' => 'DLF Cyber City, Gurgaon',
                'latitude' => 28.5022,
                'longitude' => 77.0934,
                'furnished_status' => 'furnished',
                'bedrooms' => 0,
                'bathrooms' => 2,
                'area_sqft' => 2000,
                'availability_status' => 'vacant',
                'published' => true,
                'featured' => true,
                'published_at' => now(),
                'amenities' => json_encode(['parking', 'gym', 'security', 'power_backup', 'lift']),
            ],
            [
                'org_id' => $organization->id,
                'title' => 'PG Accommodation for Girls in Indiranagar',
                'short_description' => 'Safe and comfortable PG for working women',
                'long_description' => 'This PG accommodation is specifically designed for working women. It offers safe, comfortable living with all necessary amenities and is located in a prime area of Indiranagar.',
                'property_type' => 'pg',
                'category' => 'PG',
                'price' => 15000,
                'security_deposit' => 30000,
                'city' => 'Bangalore',
                'locality' => 'Indiranagar',
                'pincode' => '560038',
                'address_line' => '100 Feet Road, Indiranagar, Bangalore',
                'latitude' => 12.9716,
                'longitude' => 77.6412,
                'furnished_status' => 'furnished',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area_sqft' => 300,
                'availability_status' => 'vacant',
                'published' => true,
                'featured' => false,
                'published_at' => now(),
                'amenities' => json_encode(['security', 'power_backup']),
            ]
        ];

        foreach ($properties as $propertyData) {
            Property::create($propertyData);
        }

        $this->command->info('Properties seeded successfully!');
    }
}