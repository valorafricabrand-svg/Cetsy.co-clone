<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PauseExpiredListingsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_auto_renews_expired_active_automatic_listing_when_wallet_has_funds(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 16, 10, 0, 0));

        $seller = User::factory()->create();
        $shop = Shop::create([
            'user_id' => $seller->id,
            'name' => 'Shop A',
            'slug' => 'shop-a',
            'language' => 'en',
            'country' => 'US',
            'currency' => 'USD',
            'bank_account' => '123456789',
            'routing_number' => '987654321',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'postal' => '12345',
        ]);
        $category = Category::create([
            'name' => 'Cat A',
            'slug' => 'cat-a',
            'listing_fee' => 2.50,
            'listing_frequency' => 4,
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Product A',
            'slug' => 'product-a',
            'description' => 'x',
            'type' => 'physical',
            'price' => 10,
            'is_active' => 1,
            'renewal_type' => 'automatic',
            'listing_paid_at' => now()->subMonths(4),
            'next_due_date' => now()->subMinute(),
            'featured_image' => 'products/x.jpg',
        ]);

        Wallet::create([
            'user_id' => $seller->id,
            'credit' => 20,
            'debit' => 0,
            'balance' => 20,
            'method' => 'wallet',
            'status' => 'completed',
            'description' => 'Seed funds',
        ]);

        $this->artisan('products:pause-expired')->assertExitCode(0);

        $product->refresh();

        $this->assertSame(1, (int) $product->is_active);
        $this->assertTrue(Carbon::parse($product->next_due_date)->gt(now()->addMonths(3)));

        $this->assertDatabaseHas('wallets', [
            'user_id' => $seller->id,
            'debit' => 2.50,
            'description' => 'Listing auto-renewal fee (4months)',
        ]);

        $this->assertDatabaseHas('payments', [
            'shop_id' => $shop->id,
            'payment_method' => 'wallet',
            'payment_name' => 'listing_fee',
            'payment_status' => 'successful',
            'total_amount' => 2.50,
        ]);
    }

    public function test_it_reactivates_expired_paused_automatic_listing_when_wallet_has_funds(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 16, 10, 0, 0));

        $seller = User::factory()->create();
        $shop = Shop::create([
            'user_id' => $seller->id,
            'name' => 'Shop B',
            'slug' => 'shop-b',
            'language' => 'en',
            'country' => 'US',
            'currency' => 'USD',
            'bank_account' => '123456789',
            'routing_number' => '987654321',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'postal' => '12345',
        ]);
        $category = Category::create([
            'name' => 'Cat B',
            'slug' => 'cat-b',
            'listing_fee' => 1.00,
            'listing_frequency' => 1,
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Product B',
            'slug' => 'product-b',
            'description' => 'x',
            'type' => 'physical',
            'price' => 15,
            'is_active' => 2,
            'renewal_type' => 'automatic',
            'listing_paid_at' => now()->subMonth(),
            'next_due_date' => now()->subMinute(),
            'featured_image' => 'products/y.jpg',
        ]);

        Wallet::create([
            'user_id' => $seller->id,
            'credit' => 3,
            'debit' => 0,
            'balance' => 3,
            'method' => 'wallet',
            'status' => 'completed',
            'description' => 'Seed funds',
        ]);

        $this->artisan('products:pause-expired')->assertExitCode(0);

        $product->refresh();
        $this->assertSame(1, (int) $product->is_active);
        $this->assertTrue(Carbon::parse($product->next_due_date)->gt(now()));
    }

    public function test_it_pauses_expired_automatic_listing_when_wallet_balance_is_insufficient(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 16, 10, 0, 0));

        $seller = User::factory()->create();
        $shop = Shop::create([
            'user_id' => $seller->id,
            'name' => 'Shop C',
            'slug' => 'shop-c',
            'language' => 'en',
            'country' => 'US',
            'currency' => 'USD',
            'bank_account' => '123456789',
            'routing_number' => '987654321',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'postal' => '12345',
        ]);
        $category = Category::create([
            'name' => 'Cat C',
            'slug' => 'cat-c',
            'listing_fee' => 9.00,
            'listing_frequency' => 4,
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Product C',
            'slug' => 'product-c',
            'description' => 'x',
            'type' => 'physical',
            'price' => 20,
            'is_active' => 1,
            'renewal_type' => 'automatic',
            'listing_paid_at' => now()->subMonths(4),
            'next_due_date' => now()->subMinute(),
            'featured_image' => 'products/z.jpg',
        ]);

        Wallet::create([
            'user_id' => $seller->id,
            'credit' => 2,
            'debit' => 0,
            'balance' => 2,
            'method' => 'wallet',
            'status' => 'completed',
            'description' => 'Seed funds',
        ]);

        $this->artisan('products:pause-expired')->assertExitCode(0);

        $product->refresh();
        $this->assertSame(2, (int) $product->is_active);

        $this->assertDatabaseMissing('wallets', [
            'user_id' => $seller->id,
            'description' => 'Listing auto-renewal fee (4months)',
            'debit' => 9.00,
        ]);
    }

    public function test_it_pauses_expired_manual_listing_without_charging_wallet(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 16, 10, 0, 0));

        $seller = User::factory()->create();
        $shop = Shop::create([
            'user_id' => $seller->id,
            'name' => 'Shop D',
            'slug' => 'shop-d',
            'language' => 'en',
            'country' => 'US',
            'currency' => 'USD',
            'bank_account' => '123456789',
            'routing_number' => '987654321',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'postal' => '12345',
        ]);
        $category = Category::create([
            'name' => 'Cat D',
            'slug' => 'cat-d',
            'listing_fee' => 5.00,
            'listing_frequency' => 4,
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Product D',
            'slug' => 'product-d',
            'description' => 'x',
            'type' => 'physical',
            'price' => 21,
            'is_active' => 1,
            'renewal_type' => 'manual',
            'listing_paid_at' => now()->subMonths(4),
            'next_due_date' => now()->subMinute(),
            'featured_image' => 'products/d.jpg',
        ]);

        Wallet::create([
            'user_id' => $seller->id,
            'credit' => 15,
            'debit' => 0,
            'balance' => 15,
            'method' => 'wallet',
            'status' => 'completed',
            'description' => 'Seed funds',
        ]);

        $this->artisan('products:pause-expired')->assertExitCode(0);

        $product->refresh();
        $this->assertSame(2, (int) $product->is_active);

        $this->assertDatabaseMissing('wallets', [
            'user_id' => $seller->id,
            'description' => 'Listing auto-renewal fee (4months)',
            'debit' => 5.00,
        ]);
    }
}
