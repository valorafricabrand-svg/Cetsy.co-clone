<?php

namespace App\Mail;

use App\Models\Kyc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportKycSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Kyc $kyc;

    /**
     * Create a new message instance.
     */
    public function __construct(Kyc $kyc)
    {
        $this->kyc = $kyc;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $name = $this->kyc->user->name ?? ($this->kyc->first_name . ' ' . $this->kyc->last_name);
        return new Envelope(
            subject: 'Seller ' . trim($name) . ' submitted a KYC (awaiting approval)',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.kyc_submitted_support',
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
        $name = $this->kyc->user->name ?? trim(($this->kyc->first_name ?? '') . ' ' . ($this->kyc->last_name ?? ''));

        // Compute admin URLs safely (route names may vary)
        $adminShowUrl = null;
        try { $adminShowUrl = route('admin.kyc.show', $this->kyc->id); } catch (\Throwable $e) { $adminShowUrl = null; }
        $adminIndexUrl = null;
        try { $adminIndexUrl = route('admin.kyc.index'); } catch (\Throwable $e) { $adminIndexUrl = url('/admin/kyc'); }

        return $this->subject('Seller ' . $name . ' submitted a KYC (awaiting approval)')
            ->view('emails.kyc_submitted_support')
            ->with([
                'kyc' => $this->kyc,
                'adminShowUrl' => $adminShowUrl ?: $adminIndexUrl,
                'adminIndexUrl' => $adminIndexUrl,
            ]);
    }
}
