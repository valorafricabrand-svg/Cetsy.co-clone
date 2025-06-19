<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the column.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // price in the currency’s smallest unit (e.g. cents) or nullable decimal
                $table->decimal('listing_fee', 12, 2)->default(0.25)->after('name');

            // ─────────────
            // If you prefer decimal:
            // $table->decimal('listing_fee', 12, 2)->default(0)->after('name');
        });
    }

    /**
     * Roll back.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('listing_fee');
        });
    }
};
