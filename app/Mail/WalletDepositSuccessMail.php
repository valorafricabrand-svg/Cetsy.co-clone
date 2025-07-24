<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Wallet;

class WalletDepositSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $wallet;
    public $amount;
    public $reference;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Wallet $wallet, $amount, $reference)
    {
        $this->user = $user;
        $this->wallet = $wallet;
        $this->amount = $amount;
        $this->reference = $reference;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Wallet Deposit Successful - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.wallet.deposit-success',
            with: [
                'user' => $this->user,
                'wallet' => $this->wallet,
                'amount' => $this->amount,
                'reference' => $this->reference,
            ],
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
} 