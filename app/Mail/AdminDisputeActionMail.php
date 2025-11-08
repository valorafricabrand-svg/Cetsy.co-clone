<?php

namespace App\Mail;

use App\Models\Dispute;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminDisputeActionMail extends Mailable
{
    use Queueable, SerializesModels;

    public Dispute $dispute;
    public User $recipient;
    public ?User $admin;
    public string $action; // e.g., 'closed', 'resolved', 'finalized', 'refunded'

    public function __construct(Dispute $dispute, User $recipient, ?User $admin, string $action)
    {
        $this->dispute = $dispute->loadMissing(['order.shop', 'buyer', 'seller', 'closedBy', 'resolvedBy']);
        $this->recipient = $recipient;
        $this->admin = $admin;
        $this->action = $action;
    }

    public function build(): self
    {
        $statusText = match ($this->action) {
            'closed' => 'closed by support',
            'refunded' => 'refunded by support',
            'finalized' => 'finalized by support',
            default => 'updated by support',
        };

        $subject = 'Dispute #' . $this->dispute->id . ' ' . $statusText;

        return $this->subject($subject)
            ->view('emails.admin_dispute_action')
            ->with([
                'dispute' => $this->dispute,
                'recipient' => $this->recipient,
                'admin' => $this->admin,
                'order' => $this->dispute->order,
                'decisionLabel' => $this->dispute->getDecisionLabel(),
                'favorOutcome' => $this->dispute->getFavorOutcomeLabel(),
                'action' => $this->action,
            ]);
    }
}

