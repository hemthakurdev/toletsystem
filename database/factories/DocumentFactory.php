<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition()
    {
        return [
            'org_id' => Organization::factory(),
            'property_id' => Property::factory(),
            'tenant_id' => Tenant::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'category' => $this->faker->randomElement(['lease_agreement', 'maintenance_report', 'inspection_report', 'insurance', 'legal', 'other']),
            'document_type' => $this->faker->randomElement(['pdf', 'doc', 'docx', 'jpg', 'png']),
            'file_path' => 'documents/' . $this->faker->uuid . '.pdf',
            'file_size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'mime_type' => 'application/pdf',
            'expires_at' => $this->faker->optional(0.7)->dateTimeBetween('now', '+1 year'),
            'is_public' => $this->faker->boolean(20),
        ];
    }

    public function leaseAgreement()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'lease_agreement',
                'document_type' => 'pdf',
                'mime_type' => 'application/pdf',
                'expires_at' => $this->faker->dateTimeBetween('+6 months', '+2 years'),
            ];
        });
    }

    public function maintenanceReport()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'maintenance_report',
                'document_type' => 'pdf',
                'mime_type' => 'application/pdf',
            ];
        });
    }

    public function expiringSoon()
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => $this->faker->dateTimeBetween('now', '+30 days'),
            ];
        });
    }
}