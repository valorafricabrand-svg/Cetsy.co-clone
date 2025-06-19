<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // when the seller pays the listing fee
            $table->timestamp('listing_paid_at')
                  ->nullable()
                  ->after('is_active');

            // when the next renewal / fee is due
            $table->timestamp('next_due_date')
                  ->nullable()
                  ->after('listing_paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['listing_paid_at', 'next_due_date']);
        });
    }
};
