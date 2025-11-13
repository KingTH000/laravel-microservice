<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GatewayController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here we define the "front door" routes for our application.
|
*/

// --- Auth Service Routes ---
// Matches: /api/auth/register, /api/auth/login
Route::any('/auth/{path}', [GatewayController::class, 'auth'])
    ->where('path', '.*');

// --- Profile Service Routes ---
// Matches: /api/profile, /api/profile/update (if we had it)
Route::any('/profile/{path?}', [GatewayController::class, 'profile'])
    ->where('path', '.*');