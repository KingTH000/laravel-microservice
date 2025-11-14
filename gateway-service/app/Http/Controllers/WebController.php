<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\MessageBag;

class WebController extends Controller
{
    // --- Registration ---

    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return view('register');
    }

    /**
     * Handle the registration form submission.
     */
    public function handleRegister(Request $request)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post('http://auth:8000/api/register', [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ]);

        if ($response->failed()) {
            if ($response->status() == 422) {
                $errors = $response->json()['errors'];
                return redirect()->back()->withErrors($errors)->withInput();
            }
            $apiError = $response->body();
            $errors = new MessageBag(['email' => "Registration failed. API said: $apiError"]);
            return redirect()->back()->withErrors($errors)->withInput();
        }

        $token = $response->json()['access_token'];
        session(['api_token' => $token]);

        // On success, go to the profile page to fill it out
        return redirect('/profile');
    }

    // --- Login / Logout ---

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Handle the login form submission.
     */
    public function handleLogin(Request $request)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post('http://auth:8000/api/login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($response->failed()) {
            $errors = new MessageBag(['email' => 'These credentials do not match our records.']);
            return redirect()->back()->withErrors($errors)->withInput();
        }

        $token = $response->json()['access_token'];
        session(['api_token' => $token]);

        return redirect('/profile');
    }

    /**
     * Handle user logout.
     */
    public function handleLogout(Request $request)
    {
        // We just need to destroy the session token
        $request->session()->forget('api_token');
        return redirect('/login');
    }

    // --- Profile ---

    /**
     * Helper to make authenticated API calls.
     */
    private function httpAuth(Request $request)
    {
        $token = $request->session()->get('api_token');
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ]);
    }

    /**
     * Show the user's profile edit page.
     */
    public function showProfile(Request $request)
    {
        // Call the internal profile service
        $response = $this->httpAuth($request)->get('http://profile:8000/api/profile');

        if ($response->failed()) {
            // Handle if the token expired or something went wrong
            return redirect('/login')->withErrors(new MessageBag(['email' => 'Your session has expired. Please log in again.']));
        }

        $profile = $response->json();
        return view('profile', ['profile' => $profile]);
    }

    /**
     * Handle the profile update form.
     */
    public function handleProfile(Request $request)
    {
        $response = $this->httpAuth($request)->post('http://profile:8000/api/profile', [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'bio' => $request->bio,
        ]);

        if ($response->failed()) {
            if ($response->status() == 422) {
                return redirect()->back()->withErrors($response->json()['errors'])->withInput();
            }
            return redirect()->back()->withErrors(new MessageBag(['bio' => 'An error occurred. Please try again.']))->withInput();
        }

        // On success, just reload the page with a success message
        return redirect('/profile')->with('success', 'Profile updated successfully!');
    }

    
}
