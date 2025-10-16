<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $organization;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create super admin role and permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Create test organization
        $this->organization = Organization::factory()->create();
        
        // Create admin user with super_admin role
        $this->adminUser = User::factory()->create([
            'org_id' => $this->organization->id,
            'email_verified_at' => now(),
        ]);
        $this->adminUser->assignRole('super_admin');
        
        // Create regular user
        $this->regularUser = User::factory()->create([
            'org_id' => $this->organization->id,
            'email_verified_at' => now(),
        ]);
        $this->regularUser->assignRole('admin');
    }

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin');

        $response->assertStatus(200);
    }

    /** @test */
    public function regular_user_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->regularUser)
            ->get('/admin');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_organizations()
    {
        Organization::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/organizations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'status',
                            'created_at',
                            'users_count',
                            'properties_count'
                        ]
                    ],
                    'meta'
                ]
            ]);
    }

    /** @test */
    public function admin_can_create_organization()
    {
        $organizationData = [
            'name' => 'Test Organization',
            'email' => 'test@organization.com',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'state' => 'Test State',
            'pincode' => '123456',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/admin/organizations', $organizationData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Organization created successfully'
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
            'email' => 'test@organization.com',
        ]);
    }

    /** @test */
    public function admin_can_suspend_organization()
    {
        $organization = Organization::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/admin/organizations/{$organization->id}/suspend");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Organization suspended successfully'
            ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'status' => 'suspended',
        ]);
    }

    /** @test */
    public function admin_can_activate_organization()
    {
        $organization = Organization::factory()->create(['status' => 'suspended']);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/admin/organizations/{$organization->id}/activate");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Organization activated successfully'
            ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_view_users()
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'status',
                            'created_at',
                            'organization'
                        ]
                    ],
                    'meta'
                ]
            ]);
    }

    /** @test */
    public function admin_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'org_id' => $this->organization->id,
            'roles' => ['admin'],
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/admin/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'org_id' => $this->organization->id,
        ]);
    }

    /** @test */
    public function admin_can_suspend_user()
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/admin/users/{$user->id}/suspend");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User suspended successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'suspended',
        ]);
    }

    /** @test */
    public function admin_can_bulk_activate_users()
    {
        $users = User::factory()->count(3)->create(['status' => 'suspended']);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/admin/users/bulk-activate', [
                'user_ids' => $users->pluck('id')->toArray()
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Users activated successfully'
            ]);

        foreach ($users as $user) {
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'status' => 'active',
            ]);
        }
    }

    /** @test */
    public function admin_can_view_plans()
    {
        Plan::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/plans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'price',
                            'properties_limit',
                            'users_limit',
                            'features',
                            'is_active'
                        ]
                    ],
                    'meta'
                ]
            ]);
    }

    /** @test */
    public function admin_can_create_plan()
    {
        $planData = [
            'name' => 'Premium Plan',
            'description' => 'Premium subscription plan',
            'price' => 999.99,
            'billing_cycle' => 'monthly',
            'properties_limit' => 100,
            'users_limit' => 10,
            'features' => ['advanced_analytics', 'priority_support'],
            'is_active' => true,
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/admin/plans', $planData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Plan created successfully'
            ]);

        $this->assertDatabaseHas('plans', [
            'name' => 'Premium Plan',
            'price' => 999.99,
        ]);
    }

    /** @test */
    public function admin_can_view_pending_listings()
    {
        Property::factory()->count(3)->create([
            'published' => false,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/listings/pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'status',
                            'published',
                            'created_at',
                            'organization'
                        ]
                    ],
                    'meta'
                ]
            ]);
    }

    /** @test */
    public function admin_can_approve_listing()
    {
        $property = Property::factory()->create([
            'published' => false,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/admin/listings/{$property->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Property approved successfully'
            ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'published' => true,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_reject_listing()
    {
        $property = Property::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/admin/listings/{$property->id}/reject", [
                'rejection_reason' => 'Insufficient information'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Property rejected successfully'
            ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'status' => 'rejected',
            'rejection_reason' => 'Insufficient information',
        ]);
    }

    /** @test */
    public function admin_can_get_analytics_overview()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/analytics/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_organizations',
                    'total_users',
                    'total_properties',
                    'total_revenue',
                    'monthly_growth',
                    'active_subscriptions'
                ]
            ]);
    }

    /** @test */
    public function admin_can_get_revenue_analytics()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/analytics/revenue');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_revenue',
                    'monthly_revenue',
                    'revenue_trends',
                    'top_organizations'
                ]
            ]);
    }

    /** @test */
    public function admin_can_get_system_health()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/analytics/system-health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'database_status',
                    'storage_usage',
                    'active_connections',
                    'queue_status',
                    'last_backup'
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_admin_panel()
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_bulk_approve_listings()
    {
        $properties = Property::factory()->count(3)->create([
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/admin/listings/bulk-approve', [
                'property_ids' => $properties->pluck('id')->toArray()
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Properties approved successfully'
            ]);

        foreach ($properties as $property) {
            $this->assertDatabaseHas('properties', [
                'id' => $property->id,
                'status' => 'active',
                'published' => true,
            ]);
        }
    }

    /** @test */
    public function admin_can_get_organization_statistics()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/organizations/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_organizations',
                    'active_organizations',
                    'suspended_organizations',
                    'monthly_registrations',
                    'top_cities'
                ]
            ]);
    }

    /** @test */
    public function admin_can_get_user_statistics()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/users/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_users',
                    'active_users',
                    'suspended_users',
                    'monthly_registrations',
                    'role_distribution'
                ]
            ]);
    }
}