<?php

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_summary_includes_completed_and_on_hold_balances(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Wallet::create([
            'user_id' => $user->id,
            'credit'  => 0,
            'debit'   => 0,
            'balance' => 125.50,
            'status'  => 'completed',
            'description' => 'Legacy balance snapshot',
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'credit'  => 0,
            'debit'   => 0,
            'balance' => 40.00,
            'status'  => 'on_hold',
            'description' => 'Legacy hold snapshot',
        ]);

        $response = $this->getJson('/api/wallet/summary');

        $response->assertOk()
            ->assertJson([
                'balance' => 125.50,
                'on_hold' => 40.00,
            ]);
    }
}
