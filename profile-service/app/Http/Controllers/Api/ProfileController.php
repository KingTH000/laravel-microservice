<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // --- Get the user's profile ---
    public function show(Request $request)
    {
        // We get the user data injected by our middleware
        $user = $request->auth_user;

        // Find the profile, or create an empty one if it doesn't exist
        $profile = Profile::firstOrCreate(
            ['user_id' => $user['id']]
        );

        return response()->json($profile);
    }

    // --- Update the user's profile ---
    public function update(Request $request)
    {
        $user = $request->auth_user; // Get user from middleware

        $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
        ]);

        // Find the profile for this user and update it.
        // updateOrCreate is perfect: it finds the profile by user_id
        // or creates a new one if it doesn't exist, then updates it.
        $profile = Profile::updateOrCreate(
            ['user_id' => $user['id']], // Find by this
            [                                // Update with this
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'bio' => $request->bio,
            ]
        );

        return response()->json($profile);
    }
}