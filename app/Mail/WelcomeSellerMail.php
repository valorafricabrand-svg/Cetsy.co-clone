<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeSellerMail extends LocalizedMailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->usePreferredLocale($user);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName = config('app.name');

        return new Envelope(
            subject: __('emails.welcome_seller.subject', ['app' => $appName]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome_seller',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        $appName = config('app.name');

        return $this->subject(__('emails.welcome_seller.subject', ['app' => $appName]))
            ->view('emails.welcome_seller')
            ->with([
                'user' => $this->user,
                'appName' => $appName,
            ]);
    }
}
