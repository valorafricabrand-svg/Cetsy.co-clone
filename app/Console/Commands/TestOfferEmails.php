<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Offer;
use App\Models\User;
use App\Models\Product;
use App\Mail\OfferDeclinedMail;
use App\Mail\OfferAcceptedMail;
use App\Mail\CounterOfferMail;
use Illuminate\Support\Facades\Mail;

class TestOfferEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:offer-emails {--type=all : Type of email to test (declined, accepted, counter, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test offer email notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        
        // Get a sample offer for testing
        $offer = Offer::with(['product', 'buyer'])->first();
        
        if (!$offer) {
            $this->error('No offers found in the database. Please create some offers first.');
            return 1;
        }

        $seller = User::first(); // Use first user as seller for testing
        
        $this->info("Testing offer emails with offer ID: {$offer->id}");
        $this->info("Buyer: {$offer->buyer->name} ({$offer->buyer->email})");
        $this->info("Product: {$offer->product->name}");
        $this->info("Offer Price: " . get_currency() . " " . number_format($offer->offer_price, 2));

        if ($type === 'all' || $type === 'declined') {
            $this->testDeclinedEmail($offer, $seller);
        }

        if ($type === 'all' || $type === 'accepted') {
            $this->testAcceptedEmail($offer, $seller);
        }

        if ($type === 'all' || $type === 'counter') {
            $this->testCounterEmail($offer, $seller);
        }

        $this->info('Email testing completed. Check the logs for email content.');
        return 0;
    }

    private function testDeclinedEmail($offer, $seller)
    {
        $this->info('Testing declined email...');
        
        try {
            Mail::to($offer->buyer->email)
                ->send(new OfferDeclinedMail($offer, $offer->product, $seller, $offer->buyer));
            
            $this->info('✅ Declined email sent successfully');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send declined email: ' . $e->getMessage());
        }
    }

    private function testAcceptedEmail($offer, $seller)
    {
        $this->info('Testing accepted email...');
        
        try {
            Mail::to($offer->buyer->email)
                ->send(new OfferAcceptedMail($offer, $offer->product, $seller, $offer->buyer));
            
            $this->info('✅ Accepted email sent successfully');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send accepted email: ' . $e->getMessage());
        }
    }

    private function testCounterEmail($offer, $seller)
    {
        $this->info('Testing counter offer email...');
        
        try {
            Mail::to($offer->buyer->email)
                ->send(new CounterOfferMail($offer, $offer->product, $seller, $offer->buyer));
            
            $this->info('✅ Counter offer email sent successfully');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send counter offer email: ' . $e->getMessage());
        }
    }
} 