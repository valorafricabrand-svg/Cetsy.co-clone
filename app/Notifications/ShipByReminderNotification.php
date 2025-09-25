<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShipByReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public string $context = 'buyer', public ?string $windowLabel = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $url = ($this->context === 'seller')
            ? route('seller.orders.show', $this->order)
            : route('buyer.orders.show', $this->order->id);

        $subject = $this->context === 'seller'
            ? 'Reminder: Ship order #'.$this->order->id
            : 'Update: Your order #'.$this->order->id.' will ship soon';

        $line = $this->windowLabel
            ? 'Ship-by window: '.$this->windowLabel
            : 'Please review your order timeline.';

        return (new MailMessage)
            ->subject($subject)
            ->line($line)
            ->action('View Order', $url);
    }

    public function toArray($notifiable)
    {
        return [
            'order_id'    => $this->order->id,
            'context'     => $this->context,
            'windowLabel' => $this->windowLabel,
        ];
    }
}

