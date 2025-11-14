<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // <-- Import the Http client

class GatewayController extends Controller
{
    /**
     * A helper to build the base Http client.
     */
    private function buildHttp(Request $request)
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        // Pass along the authorization token if it exists
        if ($request->bearerToken()) {
            $headers['Authorization'] = 'Bearer ' . $request->bearerToken();
        }
        
        return Http::withHeaders($headers);
    }

    /**
     * Handle all incoming requests for the auth service.
     */
    public function auth(Request $request, $path)
    {
        $url = 'http://auth:8000/api/' . $path;

        $response = $this->buildHttp($request)->send(
            $request->method(), 
            $url, 
            ['json' => $request->all()]
        );

        return response($response->body(), $response->status());
    }

    /**
     * Handle all incoming requests for the profile service.
     */
    public function profile(Request $request, $path)
    {
        $url = 'http://profile:8000/api/' . $path;

        $response = $this->buildHttp($request)->send(
            $request->method(),
            $url,
            ['json' => $request->all()]
        );

        return response($response->body(), $response->status());
    }
}