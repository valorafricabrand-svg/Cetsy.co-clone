<?php

namespace App\Notifications;

use App\Models\Appeal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppealClosedNotification extends Notification implements ShouldQueue
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
        $disputeId = $this->appeal->dispute_id;
        
        return (new MailMessage)
            ->subject("Appeal Closed - Dispute #{$disputeId}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your appeal for Dispute #{$disputeId} has been closed by the Cetsy support team.")
            ->line("Reason: {$this->appeal->review_notes}")
            ->line("The appeal process has ended and no further appeals can be submitted for this dispute.")
            ->action('View Dispute Details', route('disputes.show', $disputeId))
            ->line('Thank you for using Cetsy!');
    }

    public function toArray($notifiable)
    {
        return [
            'appeal_id' => $this->appeal->id,
            'dispute_id' => $this->appeal->dispute_id,
            'review_notes' => $this->appeal->review_notes,
            'message' => 'Your appeal has been closed by the support team.',
            'type' => 'appeal_closed'
        ];
    }
}
