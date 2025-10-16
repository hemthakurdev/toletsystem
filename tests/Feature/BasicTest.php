<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_functionality()
    {
        // Create test organization
        $organization = Organization::factory()->create();
        
        // Create test user
        $user = User::factory()->create([
            'org_id' => $organization->id,
            'email_verified_at' => now(),
        ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'org_id' => $organization->id,
        ]);

        $this->assertTrue(true);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
    }
}
