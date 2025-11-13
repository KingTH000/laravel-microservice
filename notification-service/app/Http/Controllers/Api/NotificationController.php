<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    public function sendWelcome(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
        ]);

        Mail::to($request->email)->send(new WelcomeEmail($request->name));

        return response()->json(['message' => 'Email sent to queue.']);
    }
}