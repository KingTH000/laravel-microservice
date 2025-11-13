<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $name // Use public property promotion
    ) {
        // Now $this->name is automatically available
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Our App!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // THIS IS THE FIX:
        // We tell Laravel to use the Blade view file
        // at 'resources/views/emails/welcome.blade.php'
        return new Content(
            view: 'emails.welcome',
        );
        // The public $name property will be automatically
        // passed to this view.
    }
}