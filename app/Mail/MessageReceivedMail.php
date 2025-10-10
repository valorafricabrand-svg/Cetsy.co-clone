<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;

class MessageReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageModel;
    public $product;
    public $sender;
    public $receiver;

    /**
     * Create a new message instance.
     */
    public function __construct(Message $message, Product $product = null, User $sender, User $receiver)
    {
        $this->messageModel = $message;
        $this->product = $product;
        $this->sender = $sender;
        $this->receiver = $receiver;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'New Message from ' . $this->sender->name;
        if ($this->product) {
            $subject .= ' about "' . $this->product->name . '"';
        }
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.message_received',
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
        $subject = 'New Message from ' . $this->sender->name;
        if ($this->product) {
            $subject .= ' about "' . $this->product->name . '"';
        }
        // Compute deep link to the conversation for the receiver
         try {
            $productId = (int) ($this->messageModel->product_id ?? 0);
            $otherId   = (int) $this->sender->id; // other participant is the sender
            $convId    = $productId . '-' . $otherId; // use 0 for direct messages
            if (method_exists($this->receiver, 'isBuyer') && $this->receiver->isBuyer()) {
                $messageUrl = route('buyer.messages.show', $convId);
            } else {
                $messageUrl = route('seller.messages.show', $convId);
            }
        } catch (\Throwable $e) {
            $messageUrl = route('notifications.index');
        }

        return $this->subject($subject)
            ->view('emails.message_received')
            ->with([
                'messageModel' => $this->messageModel,
                'product' => $this->product,
                'sender' => $this->sender,
                'receiver' => $this->receiver,
                'messageUrl' => $messageUrl,
            ]);
    }
}
