<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email|max:100',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $userRoleId = \DB::table('roles')->where('name', 'app_user')->value('id');

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $userRoleId,
        ]);

        return response()->json([
            'message' => 'Registration successful.',
            'data' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with('role')->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Auth::login($user);

        // PostgreSQL SET does not accept bind parameters -> use set_config().
        \DB::statement('SELECT set_config(?, ?, false)', ['app.user_id', (string) $user->id]);

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me()
    {
        $user = Auth::user()->load('role');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
        ]);
    }
}
