<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    /**
     * Display the password reset view for the given token.
     */
    public function showResetForm(Request $request)
    {
        $token = $request->route('token');
        $email = $request->get('email');
        
        return inertia('Auth/ResetPassword', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
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
            // Here we will attempt to reset the user's password. If it is successful we
            // will update the password on an actual user model and persist it to the
            // database. Otherwise we will parse the error and return the response.
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your password has been reset successfully.',
                ]);
            }

            // If the password was not reset, we will return an error response.
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset password using custom token validation
     */
    public function resetWithCustomToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
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
            // Find the password reset record
            $passwordReset = \DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$passwordReset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token.',
                ], 400);
            }

            // Check if token is valid (not expired - 1 hour)
            if (now()->diffInMinutes($passwordReset->created_at) > 60) {
                // Delete expired token
                \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Reset token has expired. Please request a new one.',
                ], 400);
            }

            // Verify the token
            if (!Hash::check($request->token, $passwordReset->token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid reset token.',
                ], 400);
            }

            // Find the user and update password
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Delete the used token
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Your password has been reset successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate reset token
     */
    public function validateToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $passwordReset = \DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$passwordReset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token.',
                ], 400);
            }

            // Check if token is expired (1 hour)
            if (now()->diffInMinutes($passwordReset->created_at) > 60) {
                \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Reset token has expired.',
                ], 400);
            }

            // Verify the token
            if (!Hash::check($request->token, $passwordReset->token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid reset token.',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Token is valid.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate token: ' . $e->getMessage(),
            ], 500);
        }
    }
}