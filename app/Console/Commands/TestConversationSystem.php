<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use App\Models\User;
use App\Models\Product;

class TestConversationSystem extends Command
{
    protected $signature = 'test:conversations';
    protected $description = 'Test the new conversation system';

    public function handle()
    {
        $this->info('Testing Conversation System...');

        // Get some test data
        $users = User::take(3)->get();
        $products = Product::take(2)->get();

        if ($users->count() < 2 || $products->count() < 1) {
            $this->error('Need at least 2 users and 1 product to test');
            return;
        }

        $buyer = $users[0];
        $seller = $users[1];
        $product = $products[0];

        $this->info("Testing conversation between {$buyer->name} (buyer) and {$seller->name} (seller) about {$product->name}");

        // Create some test messages
        $messages = [
            [
                'sender_id' => $buyer->id,
                'receiver_id' => $seller->id,
                'product_id' => $product->id,
                'body' => 'Hi! I\'m interested in this product. Is it still available?',
                'created_at' => now()->subDays(2)
            ],
            [
                'sender_id' => $seller->id,
                'receiver_id' => $buyer->id,
                'product_id' => $product->id,
                'body' => 'Yes, it\'s still available! Would you like to know more details?',
                'created_at' => now()->subDays(1)
            ],
            [
                'sender_id' => $buyer->id,
                'receiver_id' => $seller->id,
                'product_id' => $product->id,
                'body' => 'Great! What about shipping? How long does it take?',
                'created_at' => now()->subHours(12)
            ]
        ];

        foreach ($messages as $messageData) {
            Message::create($messageData);
        }

        $this->info('Created test messages');

        // Test conversation grouping
        $conversations = Message::where('product_id', $product->id)
            ->where(function($query) use ($buyer, $seller) {
                $query->where('sender_id', $buyer->id)->where('receiver_id', $seller->id)
                      ->orWhere('sender_id', $seller->id)->where('receiver_id', $buyer->id);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($message) use ($buyer, $seller) {
                $otherUserId = $message->sender_id == $buyer->id ? $message->receiver_id : $message->sender_id;
                return $message->product_id . '-' . $otherUserId;
            });

        $this->info("Found " . $conversations->count() . " conversation(s)");

        foreach ($conversations as $conversationId => $messages) {
            $this->info("Conversation ID: {$conversationId}");
            $this->info("Messages count: " . $messages->count());
            $this->info("Latest message: " . $messages->first()->body);
            $this->info("---");
        }

        $this->info('Conversation system test completed successfully!');
    }
} 