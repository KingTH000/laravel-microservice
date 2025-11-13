<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // <-- Import the Http client
use Symfony\Component\HttpFoundation\Response;

class AuthenticateService
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Get the token from the request's Authorization header
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 2. Send the token to the auth-service to be validated
        // We use Laravel's Http client to make a server-to-server request.
        // NOTE: In a real production app, you'd move this URL to your .env file
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('http://auth-service.test/api/user');

        // 3. Check the response from auth-service
        if ($response->failed()) {
            // Token was invalid or auth-service is down
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 4. Token is valid. Get the user data from the response.
        $user = $response->json();

        // 5. "Inject" the authenticated user's data into the request
        // so our controller can use it.
        $request->merge(['auth_user' => $user]);

        return $next($request);
    }
}