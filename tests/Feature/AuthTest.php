<?php

use Arth2o\AuthProfile\Tests\Models\User;
use Illuminate\Support\Facades\Hash;

it('returns a token and expires_at on successful login', function () {
    User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/custom-auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'token',
            'expires_at',
        ])
        ->assertJson(['message' => 'Login successful.']);

    // Token should be a string of configured length (default 80)
    expect(strlen($response->json('token')))->toBe(80);

    // Verify the token is stored as a hash in the database
    $user = User::first();
    expect($user->api_token)->toBe(hash('sha256', $response->json('token')));
    expect($user->expires_at)->not->toBeNull();
});

it('returns 401 with generic message on wrong password', function () {
    User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/custom-auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'The provided credentials are incorrect.']);
});

it('returns the same generic error for non-existent email (no user enumeration)', function () {
    $response = $this->postJson('/api/custom-auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'anypassword',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'The provided credentials are incorrect.']);
});

it('returns 422 when email or password is missing', function () {
    $response = $this->postJson('/api/custom-auth/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

it('returns 429 after exceeding login throttle limit', function () {
    User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    // Make 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/custom-auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);
    }

    // 6th attempt should be throttled
    $response = $this->postJson('/api/custom-auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(429);
});
