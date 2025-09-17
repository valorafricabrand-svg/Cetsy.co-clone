<?php

namespace App\Mail;

use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DisputeResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public Dispute $dispute;
    public DisputeMessage $message;
    public User $responder;

    public function __construct(Dispute $dispute, DisputeMessage $message, User $responder)
    {
        $this->dispute = $dispute->loadMissing(['order.shop', 'buyer', 'seller', 'createdBy']);
        $this->message = $message;
        $this->responder = $responder;
    }

    public function build(): self
    {
        $subject = 'New response on dispute #' . $this->dispute->id;

        return $this->subject($subject)
            ->view('emails.dispute_response')
            ->with([
                'dispute' => $this->dispute,
                'messageModel' => $this->message,
                'responder' => $this->responder,
                'initiator' => $this->dispute->createdBy,
                'order' => $this->dispute->order,
            ]);
    }
}
