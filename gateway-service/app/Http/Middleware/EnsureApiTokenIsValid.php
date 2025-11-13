<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if a user has a token in their session.
     * It's for the WEB routes, not the API routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('api_token')) {
            // No token, send them to the login page
            return redirect()->route('login');
        }

        return $next($request);
    }
}