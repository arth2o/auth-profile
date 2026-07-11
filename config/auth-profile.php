<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The fully qualified class name of the User model used for authentication.
    | This should be the host application's User model with 'email' and
    | 'password' columns.
    |
    */
    'user_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Token TTL (Days)
    |--------------------------------------------------------------------------
    |
    | The number of days before an API token expires. Set to null for tokens
    | that never expire (not recommended for production).
    |
    */
    'token_ttl_days' => 365,

    /*
    |--------------------------------------------------------------------------
    | Token Length
    |--------------------------------------------------------------------------
    |
    | The length of the randomly generated plaintext token. A longer token
    | provides greater entropy and security.
    |
    */
    'token_length' => 80,

];
