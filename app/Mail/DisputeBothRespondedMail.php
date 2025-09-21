<?php

namespace App\Mail;

use App\Models\Dispute;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DisputeBothRespondedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Dispute $dispute;
    public User $recipient;
    public User $otherParty;

    public function __construct(Dispute $dispute, User $recipient, User $otherParty)
    {
        $this->dispute = $dispute->loadMissing(['order.shop', 'buyer', 'seller']);
        $this->recipient = $recipient;
        $this->otherParty = $otherParty;
    }

    public function build(): self
    {
        $subject = 'Dispute #' . $this->dispute->id . ' update - both parties have responded';

        return $this->subject($subject)
            ->view('emails.dispute_both_responded')
            ->with([
                'dispute' => $this->dispute,
                'recipient' => $this->recipient,
                'otherParty' => $this->otherParty,
                'order' => $this->dispute->order,
            ]);
    }
}
