<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Form-based (session) authentication for the Blade UI.
 * Mirrors AuthController (the JSON/API twin) so the same
 * users table and roles drive both flows.
 */
class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        // Align PostgreSQL RLS session var with the authenticated user.
        DB::statement('SELECT set_config(?, ?, false)', ['app.user_id', (string) Auth::id()]);

        $request->session()->regenerate();

        return redirect()->intended(route('facilities.index'))
            ->with('success', 'Welcome back, '.Auth::user()->full_name.'!');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $userRoleId = DB::table('roles')->where('name', 'app_user')->value('id');

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $userRoleId,      // never trust a role posted from the form
        ]);

        Auth::login($user);
        DB::statement('SELECT set_config(?, ?, false)', ['app.user_id', (string) $user->id]);

        return redirect()->route('facilities.index')
            ->with('success', 'Registration successful. You are signed in as app_user.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
