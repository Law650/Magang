<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * API Authentication Controller — Login Mobile Teknisi via Sanctum.
 */
class ApiAuthController extends Controller
{
    /**
     * Login teknisi dan return Sanctum token.
     *
     * POST /api/auth/login
     * Body: { email, password }
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $validated['login'];

        $user = User::where('username', $login)
                    ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Username atau password salah.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan. Hubungi Administrator.',
            ], 403);
        }

        // Revoke old tokens, issue new one
        $user->tokens()->delete();
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role,
                    'phone' => $user->phone,
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * Get data user yang sedang login.
     *
     * GET /api/auth/me
     * Header: Authorization: Bearer {token}
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Logout — revoke current token.
     *
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }
}
