<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            // UUID-style or numeric IDs?  Use string so it fits either.
            $table->string('user_id')->nullable()->after('wallet_id');
        });

        /* -------- OPTIONAL: back-fill existing rows --------
           Assumes payout_requests.wallet_id points to wallets.id
           and wallets.user_id holds the seller ID
        ----------------------------------------------------*/
        if (Schema::hasColumn('payout_requests', 'wallet_id') && DB::getDriverName() !== 'sqlite') {
            DB::statement("
                UPDATE payout_requests pr
                JOIN   wallets w ON w.id = pr.wallet_id
                SET    pr.user_id = w.user_id
            ");
        }
    }

    public function down(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
