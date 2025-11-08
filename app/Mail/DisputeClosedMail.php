<?php

namespace App\Mail;

use App\Models\Dispute;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DisputeClosedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Dispute $dispute;
    public User $recipient;
    public ?User $closedBy;

    public function __construct(Dispute $dispute, User $recipient, ?User $closedBy)
    {
        $this->dispute = $dispute->loadMissing(['order.shop', 'buyer', 'seller', 'closedBy']);
        $this->recipient = $recipient;
        $this->closedBy = $closedBy;
    }

    public function build(): self
    {
        $subject = 'Dispute #' . $this->dispute->id . ' closed';

        return $this->subject($subject)
            ->view('emails.dispute_closed')
            ->with([
                'dispute' => $this->dispute,
                'recipient' => $this->recipient,
                'closedBy' => $this->closedBy,
                'order' => $this->dispute->order,
                'decisionLabel' => $this->dispute->getDecisionLabel(),
                'favorOutcome' => $this->dispute->getFavorOutcomeLabel(),
            ]);
    }
}
