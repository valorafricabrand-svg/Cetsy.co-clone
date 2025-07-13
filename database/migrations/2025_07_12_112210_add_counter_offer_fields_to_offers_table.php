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
        Schema::table('offers', function (Blueprint $table) {
            $table->boolean('is_counter_offer')->default(false)->after('status');
            $table->foreignId('original_offer_id')->nullable()->after('is_counter_offer')->constrained('offers')->nullOnDelete();
            $table->text('seller_notes')->nullable()->after('original_offer_id');
            $table->text('buyer_notes')->nullable()->after('seller_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropForeign(['original_offer_id']);
            $table->dropColumn(['is_counter_offer', 'original_offer_id', 'seller_notes', 'buyer_notes']);
        });
    }
};
