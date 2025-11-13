<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// This is the route the Profile service will call
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});