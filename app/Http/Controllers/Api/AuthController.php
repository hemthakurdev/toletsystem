<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\Plan;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    /**
     * Register a new organization and admin user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'organization_name' => 'required|string|max:255',
            'organization_email' => 'required|email|unique:organizations,email',
            'organization_phone' => 'nullable|string|max:20',
            'organization_address' => 'nullable|string',
            'organization_city' => 'nullable|string|max:100',
            'organization_state' => 'nullable|string|max:100',
            'organization_pincode' => 'nullable|string|max:10',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create organization
            $organization = Organization::create([
                'name' => $request->organization_name,
                'email' => $request->organization_email,
                'phone' => $request->organization_phone,
                'address' => $request->organization_address,
                'city' => $request->organization_city,
                'state' => $request->organization_state,
                'pincode' => $request->organization_pincode,
                'plan_id' => $request->plan_id ?? Plan::where('name', 'Starter')->first()->id,
                'trial_ends_at' => now()->addDays(14), // 14-day trial
            ]);

            // Create admin user
            $user = User::create([
                'org_id' => $organization->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            // Assign admin role
            $user->assignRole('admin');

            // Create auth token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Organization and user created successfully',
                'data' => [
                    'user' => $user->load('organization', 'roles'),
                    'organization' => $organization->load('plan'),
                    'token' => $token,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();
        $user->updateLastLogin();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user->load('organization', 'roles'),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('organization', 'roles');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Delete current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
            ],
        ]);
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();
            
            // Generate reset token
            $token = Str::random(64);
            $expiresAt = now()->addHours(1); // Token expires in 1 hour
            
            // Store reset token in database
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => Hash::make($token),
                    'created_at' => now(),
                    'expires_at' => $expiresAt,
                ]
            );
            
            // Send password reset email
            $this->emailService->sendPasswordResetEmail($user, $token);
            
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send password reset email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Find the reset token record
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->where('expires_at', '>', now())
                ->first();
            
            if (!$resetRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token',
                ], 400);
            }
            
            // Verify the token
            if (!Hash::check($request->token, $resetRecord->token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid reset token',
                ], 400);
            }
            
            // Find the user
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }
            
            // Update the password
            $user->update([
                'password' => Hash::make($request->password),
            ]);
            
            // Delete the reset token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
            
            // Revoke all existing tokens for security
            $user->tokens()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. Please login with your new password.',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}