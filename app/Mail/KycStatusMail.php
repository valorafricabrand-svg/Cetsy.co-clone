<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KycStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $status;
    public $admin_notes;

    /**
     * Create a new message instance.
     */
    public function __construct($status, $admin_notes = null)
    {
        $this->status = $status;
        $this->admin_notes = $admin_notes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->status) {
            'approved' => 'Your KYC Has Been Approved',
            'rejected' => 'Your KYC Has Been Rejected',
            'needs_correction' => 'Action Required: KYC Needs Correction',
            default => 'Your KYC Status Was Updated',
        };
        return new Envelope(subject: $subject);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.kyc_status',
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
        $subject = match ($this->status) {
            'approved' => 'Your KYC Has Been Approved',
            'rejected' => 'Your KYC Has Been Rejected',
            'needs_correction' => 'Action Required: KYC Needs Correction',
            default => 'Your KYC Status Was Updated',
        };
        return $this->subject($subject)
            ->view('emails.kyc_status')
            ->with([
                'status' => $this->status,
                'admin_notes' => $this->admin_notes,
            ]);
    }
}
