# API Reference — custom-auth

This document outlines the API endpoints provided by the `arth2o/auth-profile` package.

## Base URL
`/api/custom-auth`

## Endpoints

### 1. User Login
Authenticate credentials to generate a bearer token.

*   **URL:** `/login`
*   **Method:** `POST`
*   **Headers:**
    *   `Content-Type: application/json`
    *   `Accept: application/json`
*   **Rate Limit:** 5 requests per minute per IP. Returns `429 Too Many Requests` on exceed.

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "your-password"
}
```

**Success Response (200 OK):**
```json
{
    "message": "Login successful.",
    "token": "a1b2c3d4e5f6g7h8...",
    "expires_at": "2027-07-11T05:00:00+00:00"
}
```

**Error Response (401 Unauthorized):**
*Note: Returns the same generic message for non-existent users to prevent user-enumeration leaks.*
```json
{
    "message": "The provided credentials are incorrect."
}
```

**Validation Error Response (422 Unprocessable Content):**
```json
{
    "message": "The email field is required. (and/or) The password field is required.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

---

### 2. User Profile
Retrieve profile data for the authenticated user.

*   **URL:** `/profile`
*   **Method:** `GET`
*   **Headers:**
    *   `Accept: application/json`
    *   `Authorization: Bearer <token>`

**Success Response (200 OK):**
```json
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "created_at": "2026-07-11T05:00:00.000000Z",
        "updated_at": "2026-07-11T05:00:00.000000Z"
    }
}
```

**Error Response (401 Unauthenticated):**
*Note: Also returned if the token exists but is expired.*
```json
{
    "message": "Unauthenticated."
}
```
