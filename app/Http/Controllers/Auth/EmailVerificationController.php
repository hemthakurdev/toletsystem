<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmailVerificationController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Show the email verification notice.
     */
    public function show()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return inertia('Auth/VerifyEmail');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'hash' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = \App\Models\User::findOrFail($request->id);

            if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification link.',
                ], 400);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email already verified.',
                ]);
            }

            if ($user->markEmailAsVerified()) {
                // Send welcome email after verification
                $this->emailService->sendWelcomeEmail($user);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already verified.',
                ], 400);
            }

            // Send verification email
            $this->emailService->sendEmailVerificationEmail($user);

            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send verification email to unauthenticated user
     */
    public function sendVerification(Request $request): JsonResponse
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
            $user = \App\Models\User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already verified.',
                ], 400);
            }

            // Send verification email
            $this->emailService->sendEmailVerificationEmail($user);

            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check verification status
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json([
                'success' => true,
                'data' => [
                    'verified' => $user->hasVerifiedEmail(),
                    'email' => $user->email,
                    'verified_at' => $user->email_verified_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check verification status: ' . $e->getMessage(),
            ], 500);
        }
    }
}