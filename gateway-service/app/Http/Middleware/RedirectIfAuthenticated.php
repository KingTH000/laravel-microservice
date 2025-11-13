<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user has our session token
        if ($request->session()->has('api_token')) {
            // They are logged in, so redirect them to the profile page
            return redirect()->route('profile');
        }

        // They are not logged in, continue to the requested page (e.g., login form)
        return $next($request);
    }
}