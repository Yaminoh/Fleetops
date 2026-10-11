<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    private const ROLES = ['User', 'Admin'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(self::ROLES)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $passwordField = User::passwordColumnName();

        User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            $passwordField => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => $data['status'],
        ]);

        Alert::log('👤', 'User Added', "{$data['name']} was added as {$data['role']} by {$request->user()->name}.");

        return back()->with('status', 'User created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        if ($request->boolean('status_toggle')) {
            if ($user->id === $request->user()->id) {
                return back()->withErrors(['user' => 'You cannot change your own account status.']);
            }

            $data = $request->validate([
                'status' => ['required', Rule::in(['active', 'inactive'])],
            ]);

            $user->status = $data['status'];
            $user->save();

            $status = $user->status;
            Alert::log(
                $status === 'inactive' ? 'OFF' : 'ON',
                $status === 'inactive' ? 'User Deactivated' : 'User Reactivated',
                "{$user->name}'s account was marked {$status} by {$request->user()->name}.",
                $status === 'inactive' ? 'warning' : 'info',
            );

            return back()->with('status', "User account {$status} successfully.");
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(self::ROLES)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $user->name = $data['name'];
        $user->email = strtolower($data['email']);
        $user->role = $data['role'];
        $user->status = $data['status'];

        if (! empty($data['password'])) {
            $user->{User::passwordColumnName()} = Hash::make($data['password']);
        }

        $user->save();

        Alert::log('✏️', 'User Updated', "{$user->name}'s account was updated by {$request->user()->name}.");

        return back()->with('status', 'User updated successfully.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'Admin', 403, 'Only administrators can manage users.');
    }
}
