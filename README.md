# Auth Profile — Laravel 13 Package

A lightweight, secure token-based authentication and profile API package for Laravel 13. Built with [Spatie Laravel Package Tools](https://github.com/spatie/laravel-package-tools).

## Features

- **Custom token-based authentication** — Sanctum-style hashed token storage (SHA-256)
- **Configurable token expiration** — Default 365 days, adjustable via config
- **Rate-limited login** — Built-in `throttle:5,1` protection against brute force
- **No user enumeration** — Generic error messages on failed login attempts
- **Migration safety** — `Schema::hasColumn()` guards prevent duplicate column errors
- **Zero config for basic usage** — Works out of the box with standard Laravel User model

## Requirements

- PHP ^8.2
- Laravel ^13.0

## Installation

### 1. Add the repository to your host application's `composer.json`

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/arth2o/auth-profile"
        }
    ]
}
```

### 2. Require the package

```bash
composer require arth2o/auth-profile:*
```

### 3. Run migrations

```bash
php artisan migrate
```

This adds `api_token` and `expires_at` columns to your existing `users` table.

### 4. (Optional) Publish the configuration

```bash
php artisan vendor:publish --tag="auth-profile-config"
```

## Configuration

After publishing, edit `config/auth-profile.php`:

```php
return [
    // The User model class
    'user_model' => \App\Models\User::class,

    // Token lifetime in days
    'token_ttl_days' => 365,

    // Length of the generated plaintext token
    'token_length' => 80,
];
```

> **Important:** Ensure your `User` model has `api_token` and `expires_at` in the `$fillable` array.

## API Endpoints

### `POST /api/custom-auth/login`

Authenticate with email and password to receive a bearer token.

**Request:**
```json
{
    "email": "user@example.com",
    "password": "your-password"
}
```

**Success Response (200):**
```json
{
    "message": "Login successful.",
    "token": "random-80-char-plaintext-token",
    "expires_at": "2027-07-11T05:00:00+00:00"
}
```

**Error Response (401):**
```json
{
    "message": "The provided credentials are incorrect."
}
```

**Rate Limited (429):** After 5 failed attempts within 1 minute.

---

### `GET /api/custom-auth/profile`

Retrieve the authenticated user's profile.

**Headers:**
```
Authorization: Bearer <your-token>
```

**Success Response (200):**
```json
{
    "data": {
        "id": 1,
        "name": "User Name",
        "email": "user@example.com",
        "created_at": "2026-07-11T05:00:00.000000Z",
        "updated_at": "2026-07-11T05:00:00.000000Z"
    }
}
```

**Error Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

## Security Design

| Aspect | Implementation |
|---|---|
| Token storage | SHA-256 hash only; plaintext returned once at login |
| Token expiration | Configurable TTL; expired tokens auto-cleared on use |
| Rate limiting | `throttle:5,1` on login (5 attempts/minute/IP) |
| User enumeration | Same generic error for wrong email or password |
| Password verification | `Hash::check()` with bcrypt-hashed passwords |
| Response safety | Password and token hash never included in responses |

## Testing

```bash
cd packages/arth2o/auth-profile
composer install
composer test
```

## Roadmap

- [ ] CI/CD pipeline (GitHub Actions)
- [ ] Multi-token support (multiple devices)
- [ ] Token revocation endpoint (`POST /logout`)
- [ ] Optional refresh token mechanism
- [ ] Configurable route prefix

## License

MIT — see [LICENSE](LICENSE) for details.
