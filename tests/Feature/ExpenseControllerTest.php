<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;
    protected $property;

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
    }

    /** @test */
    public function authenticated_user_can_view_expenses()
    {
        // Create test expenses
        Expense::factory()->count(3)->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/org/expenses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'amount',
                            'category',
                            'status',
                            'property',
                            'created_at'
                        ]
                    ],
                    'meta'
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_create_expense()
    {
        Storage::fake('public');

        $expenseData = [
            'title' => 'Test Expense',
            'description' => 'Test expense description',
            'amount' => 1000.50,
            'category' => 'maintenance',
            'property_id' => $this->property->id,
            'expense_date' => now()->format('Y-m-d'),
            'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/org/expenses', $expenseData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Expense created successfully'
            ]);

        $this->assertDatabaseHas('expenses', [
            'title' => 'Test Expense',
            'amount' => 1000.50,
            'category' => 'maintenance',
            'org_id' => $this->organization->id,
        ]);
    }

    /** @test */
    public function expense_creation_requires_valid_data()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/org/expenses', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'amount', 'category', 'property_id']);
    }

    /** @test */
    public function authenticated_user_can_update_expense()
    {
        $expense = Expense::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
        ]);

        $updateData = [
            'title' => 'Updated Expense',
            'amount' => 1500.00,
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/org/expenses/{$expense->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Expense updated successfully'
            ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'title' => 'Updated Expense',
            'amount' => 1500.00,
        ]);
    }

    /** @test */
    public function authenticated_user_can_delete_expense()
    {
        $expense = Expense::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/org/expenses/{$expense->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Expense deleted successfully'
            ]);

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    /** @test */
    public function admin_can_approve_expense()
    {
        $expense = Expense::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/org/expenses/{$expense->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Expense approved successfully'
            ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function admin_can_reject_expense_with_reason()
    {
        $expense = Expense::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
            'status' => 'pending',
        ]);

        $rejectionData = [
            'rejection_reason' => 'Insufficient documentation',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/org/expenses/{$expense->id}/reject", $rejectionData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Expense rejected successfully'
            ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'rejected',
            'rejection_reason' => 'Insufficient documentation',
        ]);
    }

    /** @test */
    public function user_can_filter_expenses_by_category()
    {
        Expense::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
            'category' => 'maintenance',
        ]);

        Expense::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
            'category' => 'utilities',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/org/expenses?category=maintenance');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        
        $this->assertCount(1, $data);
        $this->assertEquals('maintenance', $data[0]['category']);
    }

    /** @test */
    public function user_can_get_expense_statistics()
    {
        Expense::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
            'amount' => 1000,
            'status' => 'approved',
        ]);

        Expense::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
            'amount' => 500,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/org/expenses/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_expenses',
                    'total_amount',
                    'pending_amount',
                    'approved_amount',
                    'rejected_amount',
                    'monthly_trends',
                    'category_breakdown'
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_expenses()
    {
        $response = $this->getJson('/api/v1/org/expenses');
        $response->assertStatus(401);
    }

    /** @test */
    public function user_cannot_access_other_organization_expenses()
    {
        $otherOrg = Organization::factory()->create();
        $otherUser = User::factory()->create(['org_id' => $otherOrg->id]);

        $expense = Expense::factory()->create([
            'org_id' => $this->organization->id,
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/v1/org/expenses/{$expense->id}");

        $response->assertStatus(403);
    }
}