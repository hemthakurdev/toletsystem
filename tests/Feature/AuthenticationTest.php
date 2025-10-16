<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected $organization;
    protected $user;

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
    }

    /** @test */
    public function user_can_register_with_organization()
    {
        Mail::fake();

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'organization_name' => 'Test Organization',
            'organization_email' => 'org@example.com',
            'organization_phone' => '0987654321',
            'organization_address' => '123 Test Street',
            'organization_city' => 'Test City',
            'organization_state' => 'Test State',
            'organization_pincode' => '123456',
            'terms_accepted' => true,
        ];

        $response = $this->postJson('/register', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Registration successful. Please check your email to verify your account.'
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
            'email' => 'org@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // Assert email verification was sent
        Mail::assertSent(\App\Mail\EmailVerificationEmail::class);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/login', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /** @test */
    public function user_can_request_password_reset()
    {
        Mail::fake();

        $response = $this->postJson('/forgot-password/custom', [
            'email' => $this->user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password reset link sent to your email address.'
            ]);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $this->user->email,
        ]);

        Mail::assertSent(\App\Mail\PasswordResetEmail::class);
    }

    /** @test */
    public function user_can_reset_password_with_valid_token()
    {
        // Create password reset token
        $token = 'test-token-123';
        DB::table('password_reset_tokens')->insert([
            'email' => $this->user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/reset-password/custom', [
            'email' => $this->user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Your password has been reset successfully.'
            ]);

        // Verify password was changed
        $this->user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->user->password));

        // Verify token was deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $this->user->email,
        ]);
    }

    /** @test */
    public function password_reset_fails_with_invalid_token()
    {
        $response = $this->postJson('/reset-password/custom', [
            'email' => $this->user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => 'invalid-token',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid or expired reset token.'
            ]);
    }

    /** @test */
    public function password_reset_fails_with_expired_token()
    {
        // Create expired password reset token (older than 1 hour)
        $token = 'test-token-123';
        DB::table('password_reset_tokens')->insert([
            'email' => $this->user->email,
            'token' => Hash::make($token),
            'created_at' => now()->subHours(2),
        ]);

        $response = $this->postJson('/reset-password/custom', [
            'email' => $this->user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => $token,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Reset token has expired. Please request a new one.'
            ]);
    }

    /** @test */
    public function user_can_verify_email()
    {
        $unverifiedUser = User::factory()->create([
            'org_id' => $this->organization->id,
            'email_verified_at' => null,
        ]);

        $hash = sha1($unverifiedUser->getEmailForVerification());

        $response = $this->getJson("/verify-email/{$unverifiedUser->id}/{$hash}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email verified successfully.'
            ]);

        $unverifiedUser->refresh();
        $this->assertNotNull($unverifiedUser->email_verified_at);
    }

    /** @test */
    public function user_can_resend_email_verification()
    {
        Mail::fake();

        $unverifiedUser = User::factory()->create([
            'org_id' => $this->organization->id,
            'email_verified_at' => null,
        ]);

        $this->actingAs($unverifiedUser);

        $response = $this->postJson('/email/verification-notification');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Verification email sent successfully.'
            ]);

        Mail::assertSent(\App\Mail\EmailVerificationEmail::class);
    }

    /** @test */
    public function user_can_logout()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** @test */
    public function email_availability_check_works()
    {
        $response = $this->postJson('/register/check-email', [
            'email' => 'newuser@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'available' => true,
                    'email' => 'newuser@example.com',
                ]
            ]);
    }

    /** @test */
    public function email_availability_check_detects_existing_email()
    {
        $response = $this->postJson('/register/check-email', [
            'email' => $this->user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'available' => false,
                    'email' => $this->user->email,
                ]
            ]);
    }

    /** @test */
    public function organization_email_availability_check_works()
    {
        $response = $this->postJson('/register/check-organization-email', [
            'email' => 'neworg@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'available' => true,
                    'email' => 'neworg@example.com',
                ]
            ]);
    }

    /** @test */
    public function registration_requires_terms_acceptance()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'organization_name' => 'Test Organization',
            'organization_email' => 'org@example.com',
            'terms_accepted' => false,
        ];

        $response = $this->postJson('/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['terms_accepted']);
    }

    /** @test */
    public function password_reset_requires_valid_email()
    {
        $response = $this->postJson('/forgot-password/custom', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function password_reset_requires_valid_email_format()
    {
        $response = $this->postJson('/forgot-password/custom', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}