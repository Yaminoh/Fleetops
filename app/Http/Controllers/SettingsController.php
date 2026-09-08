<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    private const THEMES = ['light', 'dark'];
    private const DATE_FORMATS = ['M d, Y', 'd/m/Y', 'Y-m-d'];
    private const LOCALES = ['en', 'fil'];

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->name = $data['name'];
        $user->email = strtolower($data['email']);
        $user->save();

        Alert::log('⚙️', 'Profile Updated', "{$user->name} updated their profile.");

        return back()->with('status', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        Alert::log('🔒', 'Password Changed', "{$user->name} changed their password.");

        return back()->with('status', 'Password changed successfully.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'theme' => ['required', Rule::in(self::THEMES)],
            'date_format' => ['required', Rule::in(self::DATE_FORMATS)],
            'locale' => ['required', Rule::in(self::LOCALES)],
        ]);

        $user->preferences = array_merge($user->preferences ?? [], $data);
        $user->save();

        return back()->with('status', 'Preferences saved.');
    }
}
