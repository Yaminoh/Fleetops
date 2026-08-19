<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const TWO_FACTOR_VALID_MINUTES = 10;

    public function create(): View { return view('login'); }

    public function register(): View { return view('register'); }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        if (! Auth::validate($credentials) || Auth::getLastAttempted()?->status !== 'active') {
            return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
        }

        $user = Auth::getLastAttempted();
        $this->issueTwoFactorCode($user);

        $request->session()->put('two_factor.user_id', $user->id);
        $request->session()->put('two_factor.remember', $request->boolean('remember'));

        return redirect()->route('two-factor.challenge');
    }

    public function showTwoFactorChallenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor.user_id')) {
            return redirect()->route('login');
        }

        return view('two-factor-challenge');
    }

    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $userId = $request->session()->get('two_factor.user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('login');
        }

        $codeValid = $user->two_factor_code !== null
            && hash_equals($user->two_factor_code, $request->input('code'))
            && $user->two_factor_expires_at?->isFuture();

        if (! $codeValid) {
            return back()->withErrors(['code' => 'That code is invalid or has expired.']);
        }

        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        $remember = $request->session()->pull('two_factor.remember', false);
        $request->session()->forget('two_factor.user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function resendTwoFactor(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor.user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->two_factor_expires_at?->subMinutes(self::TWO_FACTOR_VALID_MINUTES - 1)->isFuture()) {
            return back()->withErrors(['code' => 'Please wait a moment before requesting a new code.']);
        }

        $this->issueTwoFactorCode($user);

        return back()->with('status', 'A new verification code has been sent to your email.');
    }

    private function issueTwoFactorCode(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(self::TWO_FACTOR_VALID_MINUTES);
        $user->save();

        $user->notify(new TwoFactorCodeNotification($code, self::TWO_FACTOR_VALID_MINUTES));
    }

    public function showForgotPassword(): View
    {
        return view('forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'If that email is registered, a password reset link has been sent.');
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->password = Hash::make($password);
                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Your password has been reset. You can now sign in.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function storeRegistration(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'role' => 'Staff',
            'status' => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
