<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

// Simple welcome page
Route::get('/', function () {
    if (session('api_token')) {
        return redirect('/profile');
    }
    return view('login');
})->name('home');

// --- Public Routes (Guest) ---
Route::middleware('guest')->group(function () {
    // Show the registration form
    Route::get('/register', [WebController::class, 'showRegisterForm'])->name('register');
    // Handle the form post
    Route::post('/register', [WebController::class, 'handleRegister']);
    // Show the login form
    Route::get('/login', [WebController::class, 'showLoginForm'])->name('login');
    // Handle the login form post
    Route::post('/login', [WebController::class, 'handleLogin']);
});

// --- Protected Routes (Auth) ---
Route::middleware('auth.web')->group(function () {
    // Handle logout
    Route::post('/logout', [WebController::class, 'handleLogout'])->name('logout');

    // Show the profile page
    Route::get('/profile', [WebController::class, 'showProfile'])->name('profile');
    // Handle the profile update
    Route::post('/profile', [WebController::class, 'handleProfile']);

    // --- ✨ GEMINI API ROUTE ---
    Route::post('/generate-bio', [WebController::class, 'generateBio']);
});