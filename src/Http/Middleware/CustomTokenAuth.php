<?php

namespace Arth2o\AuthProfile\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $hashedToken = hash('sha256', $token);

        $userModel = config('auth-profile.user_model');
        $user = $userModel::where('api_token', $hashedToken)->first();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Check token expiration
        if ($user->expires_at && now()->greaterThan($user->expires_at)) {
            // Clear the expired token
            $user->update([
                'api_token' => null,
                'expires_at' => null,
            ]);

            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        auth()->setUser($user);

        return $next($request);
    }
}
