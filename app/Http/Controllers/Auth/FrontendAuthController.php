<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FrontendAuthController extends Controller
{
    /**
     * Display the frontend user registration view.
     */
    public function showRegister(): Response
    {
        return Inertia::render('Auth/FrontendRegister');
    }

    /**
     * Handle frontend user registration.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'terms_accepted' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create frontend user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'user_type' => 'frontend',
                'is_verified' => false,
                'org_id' => null, // Frontend users don't belong to organizations
            ]);

            // Assign frontend user role
            $user->assignRole('frontend_user');

            // Log the user in
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Welcome to SaleMitra.',
                'data' => [
                    'user' => $user,
                    'redirect' => '/user/dashboard',
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the frontend user login view.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Auth/FrontendLogin');
    }

    /**
     * Handle frontend user login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Allow both frontend and organization users to login
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Log the user in
        Auth::login($user, $remember);

        // Update last login
        $user->updateLastLogin();

        // Check if this is an Inertia request
        if ($request->header('X-Inertia')) {
            return redirect('/');
        }

        // Return JSON response for API calls
        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * Handle frontend user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Check if this is an Inertia request
        if ($request->header('X-Inertia')) {
            return redirect('/');
        }

        // Return JSON response for API calls
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
            'redirect' => '/',
        ]);
    }

    /**
     * Check if email is available for frontend users.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email format',
            ], 422);
        }

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'available' => !$exists,
                'email' => $request->email,
            ],
        ]);
    }
}