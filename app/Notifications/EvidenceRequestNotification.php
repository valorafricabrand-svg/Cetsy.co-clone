<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EvidenceRequest;

class EvidenceRequestNotification extends Notification
{
    use Queueable;

    public $evidenceRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(EvidenceRequest $evidenceRequest)
    {
        $this->evidenceRequest = $evidenceRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $deadline = $this->evidenceRequest->deadline->format('M d, Y \a\t g:i A');
        $daysLeft = $this->evidenceRequest->getDaysUntilDeadline();
        
        return (new MailMessage)
            ->subject('Evidence Request - Appeal #' . $this->evidenceRequest->appeal->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have received an evidence request from the Cetsy Support Team regarding Appeal #' . $this->evidenceRequest->appeal->id . '.')
            ->line('**Request Message:** ' . $this->evidenceRequest->request_message)
            ->line('**Required Evidence Types:** ' . implode(', ', $this->evidenceRequest->getRequiredEvidenceTypesList()))
            ->line('**Deadline:** ' . $deadline . ' (' . $daysLeft . ' days remaining)')
            ->action('Submit Evidence', route('evidence-requests.show', $this->evidenceRequest->id))
            ->line('Please submit your evidence before the deadline to ensure your case is properly reviewed.')
            ->line('If you have any questions, please contact our support team.')
            ->salutation('Best regards, Cetsy Support Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'evidence_request',
            'title' => 'Evidence Request Received',
            'message' => 'You have received an evidence request for Appeal #' . $this->evidenceRequest->appeal->id,
            'evidence_request_id' => $this->evidenceRequest->id,
            'appeal_id' => $this->evidenceRequest->appeal->id,
            'dispute_id' => $this->evidenceRequest->appeal->dispute_id,
            'deadline' => $this->evidenceRequest->deadline->toISOString(),
            'days_remaining' => $this->evidenceRequest->getDaysUntilDeadline(),
            'action_url' => route('evidence-requests.show', $this->evidenceRequest->id),
        ];
    }
}
