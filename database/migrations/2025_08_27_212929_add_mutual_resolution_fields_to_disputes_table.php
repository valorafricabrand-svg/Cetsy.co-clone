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
        Schema::table('disputes', function (Blueprint $table) {
            $table->text('mutual_resolution_terms')->nullable()->after('admin_notes');
            $table->timestamp('buyer_agreed_at')->nullable()->after('mutual_resolution_terms');
            $table->timestamp('seller_agreed_at')->nullable()->after('buyer_agreed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropColumn(['mutual_resolution_terms', 'buyer_agreed_at', 'seller_agreed_at']);
        });
    }
};
