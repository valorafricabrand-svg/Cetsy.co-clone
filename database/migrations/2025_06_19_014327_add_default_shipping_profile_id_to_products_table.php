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
            // Add nullable foreign key column
            $table->foreignId('default_shipping_profile_id')
                  ->nullable()
                  ->constrained('shipping_profiles')
                  ->nullOnDelete()
                  ->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop foreign key then column
            $table->dropForeign(['default_shipping_profile_id']);
            $table->dropColumn('default_shipping_profile_id');
        });
    }
};
