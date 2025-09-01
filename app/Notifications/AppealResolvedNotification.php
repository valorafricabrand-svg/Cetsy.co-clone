<?php

namespace App\Notifications;

use App\Models\Appeal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppealResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appeal;

    public function __construct(Appeal $appeal)
    {
        $this->appeal = $appeal;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $decision = ucfirst($this->appeal->decision);
        $disputeId = $this->appeal->dispute_id;
        
        return (new MailMessage)
            ->subject("Appeal {$decision} - Dispute #{$disputeId}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your appeal for Dispute #{$disputeId} has been {$decision} by the Cetsy support team.")
            ->line("Review Notes: {$this->appeal->review_notes}")
            ->line("This decision is final and the dispute has been resolved.")
            ->action('View Dispute Details', route('disputes.show', $disputeId))
            ->line('Thank you for using Cetsy!');
    }

    public function toArray($notifiable)
    {
        return [
            'appeal_id' => $this->appeal->id,
            'dispute_id' => $this->appeal->dispute_id,
            'decision' => $this->appeal->decision,
            'review_notes' => $this->appeal->review_notes,
            'message' => "Your appeal has been {$this->appeal->decision} by the support team.",
            'type' => 'appeal_resolved'
        ];
    }
}
