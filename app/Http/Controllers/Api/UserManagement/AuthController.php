<?php

namespace App\Http\Controllers\Api\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:7'],
        ]);

        if (! auth()->attempt($validated)) {
            return response()->json([
                'message' => 'Invalid Credentials',
                'errors' => [
                    'password' => [
                        'Invalid credentials',
                    ],
                ],
            ], 422);
        }
        $user = User::with('role.permissions')->where('email', $request->input('email'))->first();

        return response()->json([
            'message' => 'Signed In Successfully',
            'access_token' => $request->user()->createToken('auth-token')->plainTextToken,
            'permissions' => $user->role->permissions->pluck('title'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'data' => '',
            'message' => 'Logged Out Successfully',
        ]);
    }
}
