<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWelcomeNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $email;
    protected string $name;

    public function __construct(string $email, string $name)
    {
        $this->email = $email;
        $this->name = $name;
    }

    public function handle(): void
    {
        $url = 'http://notification:8000/api/send-welcome'; // For clarity

        // Log that we are about to try
        Log::info('Attempting to send welcome notification for: ' . $this->email);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post($url, [
            'email' => $this->email,
            'name' => $this->name,
        ]);

        
        // If the response was not successful (4xx or 5xx),
        // throw an exception. This will cause the job to
        // fail properly so the queue worker knows.
        if ($response->failed()) {
            Log::error('Failed to send notification. Response: ' . $response->body());
            $response->throw(); // Re-throw the exception
        }

        Log::info('Successfully sent welcome notification for: ' . $this->email);
        
    }
}