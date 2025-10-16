<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;
    protected $property;
    protected $tenant;

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
        
        // Create test property
        $this->property = Property::factory()->create([
            'org_id' => $this->organization->id,
        ]);

        // Create test tenant
        $this->tenant = Tenant::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
        ]);
    }

    /** @test */
    public function authenticated_user_can_view_documents()
    {
        // Create test documents
        Document::factory()->count(3)->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/org/documents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'category',
                            'document_type',
                            'file_path',
                            'file_size',
                            'expires_at',
                            'property',
                            'tenant',
                            'created_at'
                        ]
                    ],
                    'meta'
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_upload_document()
    {
        Storage::fake('public');

        $documentData = [
            'title' => 'Test Document',
            'description' => 'Test document description',
            'category' => 'lease_agreement',
            'document_type' => 'pdf',
            'property_id' => $this->property->id,
            'tenant_id' => $this->tenant->id,
            'expires_at' => now()->addYear()->format('Y-m-d'),
            'file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/org/documents', $documentData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Document uploaded successfully'
            ]);

        $this->assertDatabaseHas('documents', [
            'title' => 'Test Document',
            'category' => 'lease_agreement',
            'org_id' => $this->organization->id,
        ]);

        // Assert file was stored
        Storage::disk('public')->assertExists('documents/' . basename($response->json('data.file_path')));
    }

    /** @test */
    public function document_upload_requires_valid_data()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/org/documents', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'category', 'document_type', 'file']);
    }

    /** @test */
    public function document_upload_validates_file_type()
    {
        $documentData = [
            'title' => 'Test Document',
            'category' => 'lease_agreement',
            'document_type' => 'pdf',
            'property_id' => $this->property->id,
            'file' => UploadedFile::fake()->create('document.txt', 100, 'text/plain'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/org/documents', $documentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function authenticated_user_can_update_document()
    {
        $document = Document::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
        ]);

        $updateData = [
            'title' => 'Updated Document',
            'description' => 'Updated description',
            'expires_at' => now()->addYear()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/org/documents/{$document->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Document updated successfully'
            ]);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Updated Document',
        ]);
    }

    /** @test */
    public function authenticated_user_can_delete_document()
    {
        Storage::fake('public');
        
        $document = Document::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/org/documents/{$document->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    /** @test */
    public function authenticated_user_can_download_document()
    {
        Storage::fake('public');
        
        $document = Document::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/org/documents/{$document->id}/download");

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_filter_documents_by_category()
    {
        Document::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
            'category' => 'lease_agreement',
        ]);

        Document::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
            'category' => 'maintenance_report',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/org/documents?category=lease_agreement');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        
        $this->assertCount(1, $data);
        $this->assertEquals('lease_agreement', $data[0]['category']);
    }

    /** @test */
    public function user_can_get_document_categories()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/org/documents/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'categories'
                ]
            ]);
    }

    /** @test */
    public function user_can_get_document_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/org/documents/types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'types'
                ]
            ]);
    }

    /** @test */
    public function user_can_get_document_statistics()
    {
        Document::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
            'file_size' => 1024,
        ]);

        Document::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
            'file_size' => 2048,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/org/documents/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_documents',
                    'total_storage_used',
                    'category_breakdown',
                    'expiring_soon',
                    'monthly_uploads'
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_documents()
    {
        $response = $this->getJson('/api/v1/org/documents');
        $response->assertStatus(401);
    }

    /** @test */
    public function user_cannot_access_other_organization_documents()
    {
        $otherOrg = Organization::factory()->create();
        $otherUser = User::factory()->create(['org_id' => $otherOrg->id]);

        $document = Document::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/v1/org/documents/{$document->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function document_file_size_is_validated()
    {
        $documentData = [
            'title' => 'Test Document',
            'category' => 'lease_agreement',
            'document_type' => 'pdf',
            'property_id' => $this->property->id,
            'file' => UploadedFile::fake()->create('document.pdf', 25000, 'application/pdf'), // 25MB
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/org/documents', $documentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }
}