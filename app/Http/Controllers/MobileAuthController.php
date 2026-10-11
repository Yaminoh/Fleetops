<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use App\Notifications\Channels\BrevoApiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MobileAuthController extends Controller
{
    private const TWO_FACTOR_VALID_MINUTES = 10;

    /**
     * Login from the Flutter mobile application.
     *
     * POST /api/mobile/login
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::validate($credentials)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $user = Auth::getLastAttempted();

        if (! $user instanceof User || $user->status !== 'active') {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        /*
         * Remove any old Sanctum tokens when starting
         * a new mobile login.
         */
        $user->tokens()->where('name', 'fleetops-mobile')->delete();

        /*
         * Generate a new 6-digit verification code.
         */
        $code = (string) random_int(100000, 999999);

        $user->two_factor_code = $code;
        $user->two_factor_expires_at = Carbon::now()
            ->addMinutes(self::TWO_FACTOR_VALID_MINUTES);

        $user->save();

        /*
         * Send the code using the existing Brevo notification.
         */
        try {
            $user->notify(
                new TwoFactorCodeNotification(
                    $code,
                    self::TWO_FACTOR_VALID_MINUTES
                )
            );
        } catch (BrevoApiException $exception) {
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
            $user->save();

            report($exception);

            return response()->json([
                'message' => 'We could not send a verification code. Please try again.',
            ], 500);
        }

        return response()->json([
            'message' => 'A verification code has been sent to your email.',
            'requires_2fa' => true,
            'expires_in_minutes' => self::TWO_FACTOR_VALID_MINUTES,
        ]);
    }

    /**
     * Verify the 2FA code and create a Sanctum token.
     *
     * POST /api/mobile/verify-2fa
     */
    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', strtolower($data['email']))
            ->first();

        if (! $user || $user->status !== 'active') {
            return response()->json([
                'message' => 'Invalid verification request.',
            ], 401);
        }

        $codeValid =
            $user->two_factor_code !== null
            && hash_equals(
                (string) $user->two_factor_code,
                (string) $data['code']
            )
            && $user->two_factor_expires_at?->isFuture();

        if (! $codeValid) {
            return response()->json([
                'message' => 'That code is invalid or has expired.',
            ], 422);
        }

        /*
         * Clear the 2FA code after successful verification.
         */
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        /*
         * Remove an existing mobile token before creating
         * a new one.
         */
        $user->tokens()
            ->where('name', 'fleetops-mobile')
            ->delete();

        /*
         * Create the Sanctum API token.
         */
        $token = $user->createToken(
            'fleetops-mobile'
        )->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ],
        ]);
    }

    /**
     * Resend the 2FA code.
     *
     * POST /api/mobile/resend-2fa
     */
    public function resendTwoFactor(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', strtolower($data['email']))
            ->first();

        if (! $user || $user->status !== 'active') {
            return response()->json([
                'message' => 'Unable to send a verification code.',
            ], 401);
        }

        /*
         * Prevent immediately generating another code
         * while the current one is still valid.
         */
        if (
            $user->two_factor_expires_at?->subMinutes(
                self::TWO_FACTOR_VALID_MINUTES - 1
            )->isFuture()
        ) {
            return response()->json([
                'message' => 'Please wait a moment before requesting a new code.',
            ], 429);
        }

        $code = (string) random_int(100000, 999999);

        $user->two_factor_code = $code;
        $user->two_factor_expires_at = Carbon::now()
            ->addMinutes(self::TWO_FACTOR_VALID_MINUTES);

        $user->save();

        try {
            $user->notify(
                new TwoFactorCodeNotification(
                    $code,
                    self::TWO_FACTOR_VALID_MINUTES
                )
            );
        } catch (BrevoApiException $exception) {
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
            $user->save();

            report($exception);

            return response()->json([
                'message' => 'We could not send a verification code. Please try again.',
            ], 500);
        }

        return response()->json([
            'message' => 'A new verification code has been sent to your email.',
            'expires_in_minutes' => self::TWO_FACTOR_VALID_MINUTES,
        ]);
    }

    /**
     * Get the currently authenticated mobile user.
     *
     * GET /api/mobile/user
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ],
        ]);
    }

    /**
     * Logout the current mobile session.
     *
     * POST /api/mobile/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}