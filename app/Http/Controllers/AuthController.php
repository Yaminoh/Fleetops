<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use App\Notifications\Channels\BrevoApiException;

class AuthController extends Controller
{
    private const TWO_FACTOR_VALID_MINUTES = 10;

    public function create(): View { return view('login'); }

    public function register(): View { return view('register'); }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        if (! Auth::validate($credentials)) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
        }

        $user = Auth::getLastAttempted();

        if (! $user instanceof User || $user->status !== 'active') {
            return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
        }

        if (! $this->issueTwoFactorCode($user)) {
            return back()
                ->withErrors(['email' => 'We could not send a verification code. Please try again.'])
                ->onlyInput('email');
        }

        $request->session()->put('two_factor.user_id', $user->id);

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
        $request->validate(['code' => ['required', 'string', 'regex:/^[0-9]{6}$/']]);

        $userId = $request->session()->get('two_factor.user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user || $user->status !== 'active') {
            $request->session()->forget('two_factor');
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

        $request->session()->forget('two_factor');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to($this->dashboardPath($user));
    }

    public function resendTwoFactor(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor.user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user || $user->status !== 'active') {
            $request->session()->forget('two_factor');
            return redirect()->route('login');
        }

        if ($user->two_factor_expires_at?->copy()->subMinutes(self::TWO_FACTOR_VALID_MINUTES - 1)->isFuture()) {
            return back()->withErrors(['code' => 'Please wait a moment before requesting a new code.']);
        }

        if (! $this->issueTwoFactorCode($user)) {
            return back()->withErrors(['code' => 'We could not send a verification code. Please try again.']);
        }

        return back()->with('status', 'A new verification code has been sent to your email.');
    }

    private function issueTwoFactorCode(User $user): bool
    {
        $code = (string) random_int(100000, 999999);

        $user->two_factor_code = $code;
        $user->two_factor_expires_at = Carbon::now()->addMinutes(self::TWO_FACTOR_VALID_MINUTES);
        $user->save();

        try {
            $user->notify(new TwoFactorCodeNotification($code, self::TWO_FACTOR_VALID_MINUTES));
        } catch (BrevoApiException $exception) {
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
            $user->save();

            report($exception);

            return false;
        }

        return true;
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
                $user->setPassword($password);
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

        $user = new User([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'role' => 'User',
            'status' => 'active',
        ]);
        $user->setPassword($data['password']);
        $user->save();

        Alert::log('🆕', 'New User Registered', "{$user->name} ({$user->email}) just signed up.");

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/users/dashboard');
    }

    private function dashboardPath(User $user): string
    {
        return $user->role === 'Admin' ? '/dashboard' : '/users/dashboard';
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
