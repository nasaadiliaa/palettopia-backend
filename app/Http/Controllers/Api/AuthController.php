<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register can still create token (or you may want to create user without token)
    public function register(Request $req)
    {
        $data = $req->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'role'     => 'customer',
            'password' => Hash::make($data['password']),
        ]);

        // For SPA session-based flows we usually don't return tokens here.
        return response()->json(['user' => $user], 201);
    }

    // Session-based login for Sanctum SPA
    public function login(Request $req)
    {
        $req->validate(['email' => 'required|email', 'password' => 'required|string']);

        $credentials = $req->only('email', 'password');

        // Attempt to login via session
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Regenerate session id to prevent fixation
        $req->session()->regenerate();

        // Return authenticated user
        return response()->json(['user' => $req->user()]);
    }

    // Logout: invalidate session
    public function logout(Request $req)
    {
        Auth::logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $req)
    {
        return response()->json($req->user());
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);
        $user = $request->user();
        $user->update($data);
        return response()->json([
            'message' => 'Profile updated',
            'user'    => $user,
        ]);
    }
}
