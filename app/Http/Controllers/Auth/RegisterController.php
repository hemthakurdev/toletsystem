<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle a registration request for the application.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'organization_name' => 'required|string|max:255',
            'organization_email' => 'required|string|email|max:255|unique:organizations,email',
            'organization_phone' => 'nullable|string|max:20',
            'organization_address' => 'nullable|string|max:500',
            'organization_city' => 'nullable|string|max:100',
            'organization_state' => 'nullable|string|max:100',
            'organization_pincode' => 'nullable|string|max:10',
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
            \DB::beginTransaction();

            // Create organization
            $organization = Organization::create([
                'name' => $request->organization_name,
                'email' => $request->organization_email,
                'phone' => $request->organization_phone,
                'address' => $request->organization_address,
                'city' => $request->organization_city,
                'state' => $request->organization_state,
                'pincode' => $request->organization_pincode,
                'status' => 'active',
            ]);

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'org_id' => $organization->id,
                'email_verified_at' => null, // Will be verified via email
            ]);

            // Assign admin role to the user
            $user->assignRole('admin');

            // Send email verification
            $this->emailService->sendEmailVerificationEmail($user);

            \DB::commit();

            // Log the user in
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Please check your email to verify your account.',
                'data' => [
                    'user' => $user,
                    'organization' => $organization,
                ],
            ], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register user for existing organization
     */
    public function storeForOrganization(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'org_id' => 'required|exists:organizations,id',
            'invitation_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Verify invitation token if provided
            if ($request->invitation_token) {
                // Check if invitation token exists and is valid
                $invitation = \DB::table('organization_invitations')
                    ->where('token', $request->invitation_token)
                    ->where('organization_id', $request->org_id)
                    ->where('status', 'pending')
                    ->where('expires_at', '>', now())
                    ->first();

                if (!$invitation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or expired invitation token',
                    ], 400);
                }

                // Check if the email matches the invitation
                if ($invitation->email !== $request->email) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Email does not match the invitation',
                    ], 400);
                }
            }

            $organization = Organization::findOrFail($request->org_id);

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'org_id' => $organization->id,
                'email_verified_at' => null,
            ]);

            // Assign staff role by default
            $user->assignRole('staff');

            // Send email verification
            $this->emailService->sendEmailVerificationEmail($user);

            // Mark invitation as accepted if token was provided
            if ($request->invitation_token) {
                \DB::table('organization_invitations')
                    ->where('token', $request->invitation_token)
                    ->update([
                        'status' => 'accepted',
                        'accepted_at' => now(),
                        'accepted_by' => $user->id,
                    ]);
            }

            // Log the user in
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Please check your email to verify your account.',
                'data' => [
                    'user' => $user,
                    'organization' => $organization,
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
     * Check if email is available
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

    /**
     * Check if organization email is available
     */
    public function checkOrganizationEmail(Request $request): JsonResponse
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

        $exists = Organization::where('email', $request->email)->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'available' => !$exists,
                'email' => $request->email,
            ],
        ]);
    }
}