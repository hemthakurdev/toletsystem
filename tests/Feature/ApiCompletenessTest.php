<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Document;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApiCompletenessTest extends TestCase
{
    use RefreshDatabase;

    protected $organization;
    protected $user;
    protected $properties;
    protected $tenants;
    protected $invoices;
    protected $expenses;
    protected $documents;
    protected $leads;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test organization
        $this->organization = Organization::factory()->create();
        
        // Create test user
        $this->user = User::factory()->create([
            'org_id' => $this->organization->id,
            'email_verified_at' => now(),
        ]);

        // Create test data
        $this->properties = Property::factory()->count(5)->create([
            'org_id' => $this->organization->id,
        ]);

        $this->tenants = Tenant::factory()->count(3)->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->properties->first()->id,
        ]);

        $this->invoices = Invoice::factory()->count(4)->create([
            'org_id' => $this->organization->id,
            'tenant_id' => $this->tenants->first()->id,
            'property_id' => $this->properties->first()->id,
        ]);

        $this->expenses = Expense::factory()->count(3)->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->properties->first()->id,
        ]);

        $this->documents = Document::factory()->count(2)->create([
            'org_id' => $this->organization->id,
        ]);

        $this->leads = Lead::factory()->count(3)->create([
            'property_id' => $this->properties->first()->id,
        ]);
    }

    /** @test */
    public function bulk_delete_properties_works()
    {
        $this->actingAs($this->user);

        $propertyIds = $this->properties->take(2)->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/bulk/properties/delete', [
            'property_ids' => $propertyIds,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully deleted 2 properties',
            ]);

        $this->assertDatabaseMissing('properties', [
            'id' => $propertyIds[0],
        ]);
    }

    /** @test */
    public function bulk_update_property_status_works()
    {
        $this->actingAs($this->user);

        $propertyIds = $this->properties->take(2)->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/bulk/properties/update-status', [
            'property_ids' => $propertyIds,
            'status' => 'inactive',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully updated 2 properties',
            ]);

        foreach ($propertyIds as $id) {
            $this->assertDatabaseHas('properties', [
                'id' => $id,
                'status' => 'inactive',
            ]);
        }
    }

    /** @test */
    public function bulk_generate_invoices_works()
    {
        $this->actingAs($this->user);

        $tenantIds = $this->tenants->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/bulk/invoices/generate', [
            'tenant_ids' => $tenantIds,
            'amount' => 50000,
            'description' => 'Monthly Rent',
            'due_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully generated 3 invoices',
            ]);

        $this->assertDatabaseHas('invoices', [
            'description' => 'Monthly Rent',
            'amount' => 50000,
        ]);
    }

    /** @test */
    public function bulk_approve_expenses_works()
    {
        $this->actingAs($this->user);

        $expenseIds = $this->expenses->where('status', 'pending')->pluck('id')->toArray();

        if (empty($expenseIds)) {
            $this->markTestSkipped('No pending expenses to approve');
        }

        $response = $this->postJson('/api/v1/bulk/expenses/approve', [
            'expense_ids' => $expenseIds,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        foreach ($expenseIds as $id) {
            $this->assertDatabaseHas('expenses', [
                'id' => $id,
                'status' => 'approved',
            ]);
        }
    }

    /** @test */
    public function advanced_property_search_works()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/search/properties/advanced', [
            'query' => 'test',
            'filters' => [
                'status' => ['active'],
                'property_type' => ['apartment'],
                'price_min' => 10000,
                'price_max' => 100000,
            ],
            'sort_by' => 'price',
            'sort_order' => 'asc',
            'per_page' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'properties',
                    'suggestions',
                    'filters_applied',
                    'total_results',
                ],
            ]);
    }

    /** @test */
    public function advanced_tenant_search_works()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/search/tenants/advanced', [
            'query' => 'test',
            'filters' => [
                'status' => ['active'],
                'property_id' => [$this->properties->first()->id],
            ],
            'sort_by' => 'name',
            'sort_order' => 'asc',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'tenants',
                    'filters_applied',
                    'total_results',
                ],
            ]);
    }

    /** @test */
    public function advanced_invoice_search_works()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/search/invoices/advanced', [
            'query' => 'INV',
            'filters' => [
                'status' => ['pending', 'paid'],
                'amount_min' => 1000,
                'amount_max' => 100000,
            ],
            'sort_by' => 'amount',
            'sort_order' => 'desc',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'invoices',
                    'filters_applied',
                    'total_results',
                ],
            ]);
    }

    /** @test */
    public function global_search_works()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/search/global', [
            'query' => 'test',
            'entities' => ['properties', 'tenants', 'invoices'],
            'limit' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'results',
                    'query',
                    'total_results',
                ],
            ]);
    }

    /** @test */
    public function search_suggestions_work()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/search/suggestions', [
            'type' => 'properties',
            'query' => 'test',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function export_properties_to_excel_works()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/exports/properties/excel', [
            'filters' => [
                'status' => ['active'],
                'property_type' => ['apartment'],
            ],
            'date_from' => now()->subYear()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Properties exported successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'filename',
                    'download_url',
                    'total_records',
                ],
            ]);
    }

    /** @test */
    public function export_tenants_to_excel_works()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/exports/tenants/excel', [
            'filters' => [
                'status' => ['active'],
            ],
            'date_from' => now()->subYear()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Tenants exported successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'filename',
                    'download_url',
                    'total_records',
                ],
            ]);
    }

    /** @test */
    public function export_invoices_to_pdf_works()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/exports/invoices/pdf', [
            'filters' => [
                'status' => ['pending', 'paid'],
            ],
            'date_from' => now()->subYear()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Invoices exported successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'filename',
                    'download_url',
                    'total_records',
                ],
            ]);
    }

    /** @test */
    public function export_financial_report_to_excel_works()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/exports/financial/excel', [
            'date_from' => now()->subYear()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
            'include_breakdown' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Financial report exported successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'filename',
                    'download_url',
                    'date_range',
                ],
            ]);
    }

    /** @test */
    public function get_export_history_works()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/exports/history');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function cleanup_old_exports_works()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/exports/cleanup');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function custom_webhook_works()
    {
        $response = $this->postJson('/api/v1/webhooks/custom/property', [
            'event' => 'property.created',
            'data' => [
                'property_id' => 123,
                'title' => 'Test Property',
                'status' => 'active',
            ],
            'timestamp' => now()->toISOString(),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function test_webhook_works()
    {
        $response = $this->postJson('/api/v1/webhooks/test', [
            'webhook_type' => 'property',
            'test_data' => [
                'event' => 'property.created',
                'property_id' => 123,
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Webhook test successful',
            ]);
    }

    /** @test */
    public function get_webhook_stats_works()
    {
        $response = $this->getJson('/api/v1/webhooks/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function bulk_operation_statistics_work()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/bulk/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'properties',
                    'tenants',
                    'invoices',
                    'expenses',
                    'documents',
                    'leads',
                ],
            ]);
    }

    /** @test */
    public function bulk_import_properties_works()
    {
        $this->actingAs($this->user);

        Storage::fake('local');

        $csvContent = "title,description,property_type,category,price,bedrooms,bathrooms,area_sqft,address,city,state,pincode\n";
        $csvContent .= "Test Property 1,Description 1,apartment,rent,50000,2,2,1000,Address 1,City 1,State 1,123456\n";
        $csvContent .= "Test Property 2,Description 2,house,sale,100000,3,3,1500,Address 2,City 2,State 2,654321\n";

        $file = UploadedFile::fake()->createWithContent('properties.csv', $csvContent);

        $response = $this->postJson('/api/v1/bulk/properties/import', [
            'csv_file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully imported 2 properties',
            ]);
    }

    /** @test */
    public function bulk_operations_require_authentication()
    {
        $response = $this->postJson('/api/v1/bulk/properties/delete', [
            'property_ids' => [1, 2, 3],
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function advanced_search_requires_authentication()
    {
        $response = $this->postJson('/api/v1/search/properties/advanced', [
            'query' => 'test',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function export_requires_authentication()
    {
        $response = $this->postJson('/api/v1/exports/properties/excel', [
            'filters' => [],
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function bulk_operations_validate_input()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/bulk/properties/delete', [
            'property_ids' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['property_ids']);
    }

    /** @test */
    public function advanced_search_validates_input()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/search/properties/advanced', [
            'filters' => [
                'price_min' => 'invalid',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['filters.price_min']);
    }

    /** @test */
    public function export_validates_input()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/exports/financial/excel', [
            'date_from' => 'invalid-date',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_from']);
    }
}