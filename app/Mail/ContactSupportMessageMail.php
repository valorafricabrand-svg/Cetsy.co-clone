<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactSupportMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function envelope(): Envelope
    {
        $subject = (string) ($this->payload['subject'] ?? 'New contact request');

        return new Envelope(
            subject: 'Contact Us: ' . $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_support_message',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        $subject = (string) ($this->payload['subject'] ?? 'New contact request');
        $email = (string) ($this->payload['email'] ?? '');
        $name = (string) ($this->payload['name'] ?? 'Website Visitor');

        return $this->subject('Contact Us: ' . $subject)
            ->replyTo($email, $name)
            ->view('emails.contact_support_message')
            ->with(['payload' => $this->payload]);
    }
}
