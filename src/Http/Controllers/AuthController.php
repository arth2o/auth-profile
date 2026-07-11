<?php

namespace Arth2o\AuthProfile\Http\Controllers;

use Arth2o\AuthProfile\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $userModel = config('auth-profile.user_model');

        $user = $userModel::where('email', $request->validated('email'))->first();

        // Generic error — no user-enumeration leak
        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        // Generate plaintext token
        $plainTextToken = Str::random(config('auth-profile.token_length', 80));

        // Store only the hash
        $expiresAt = now()->addDays(config('auth-profile.token_ttl_days', 365));

        $user->update([
            'api_token' => hash('sha256', $plainTextToken),
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'message' => 'Login successful.',
            'token' => $plainTextToken,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }
}
