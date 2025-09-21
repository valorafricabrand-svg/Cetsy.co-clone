<?php

namespace App\Mail;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DisputeInitiatedSellerMail extends Mailable
{
    use Queueable, SerializesModels;

    public Dispute $dispute;

    public function __construct(Dispute $dispute)
    {
        $this->dispute = $dispute->loadMissing(['order.shop', 'buyer', 'seller']);
    }

    public function build(): self
    {
        $subject = 'Dispute #' . $this->dispute->id . ' opened by buyer';

        return $this->subject($subject)
            ->view('emails.dispute_initiated_seller')
            ->with([
                'dispute' => $this->dispute,
                'buyer' => $this->dispute->buyer,
                'seller' => $this->dispute->seller,
                'order' => $this->dispute->order,
            ]);
    }
}
