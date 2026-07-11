<?php

use Arth2o\AuthProfile\Tests\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

it('returns 200 and user data with a valid token', function () {
    $plainToken = Str::random(80);

    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'api_token' => hash('sha256', $plainToken),
        'expires_at' => now()->addDays(365),
    ]);

    $response = $this->getJson('/api/custom-auth/profile', [
        'Authorization' => 'Bearer ' . $plainToken,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'created_at', 'updated_at'],
        ])
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => 'Test User',
                'email' => 'test@example.com',
            ],
        ]);

    // Verify password and api_token are NOT in the response
    $responseData = $response->json('data');
    expect($responseData)->not->toHaveKey('password');
    expect($responseData)->not->toHaveKey('api_token');
});

it('returns 401 when no token is provided', function () {
    $response = $this->getJson('/api/custom-auth/profile');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('returns 401 with an invalid token', function () {
    $response = $this->getJson('/api/custom-auth/profile', [
        'Authorization' => 'Bearer invalid-token-value',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('returns 401 and clears an expired token', function () {
    $plainToken = Str::random(80);

    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'api_token' => hash('sha256', $plainToken),
        'expires_at' => now()->subDay(), // Expired yesterday
    ]);

    $response = $this->getJson('/api/custom-auth/profile', [
        'Authorization' => 'Bearer ' . $plainToken,
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);

    // Verify the token was cleared from the database
    $user->refresh();
    expect($user->api_token)->toBeNull();
    expect($user->expires_at)->toBeNull();
});

it('works through the full login-then-profile flow', function () {
    User::create([
        'name' => 'Integration User',
        'email' => 'integration@example.com',
        'password' => Hash::make('securepass'),
    ]);

    // Step 1: Login to get a token
    $loginResponse = $this->postJson('/api/custom-auth/login', [
        'email' => 'integration@example.com',
        'password' => 'securepass',
    ]);

    $loginResponse->assertStatus(200);
    $token = $loginResponse->json('token');

    // Step 2: Use the token to access profile
    $profileResponse = $this->getJson('/api/custom-auth/profile', [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $profileResponse->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'Integration User',
                'email' => 'integration@example.com',
            ],
        ]);
});
