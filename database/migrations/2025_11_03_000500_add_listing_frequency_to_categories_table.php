<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Number of months a listing runs (allowed: 1 or 4)
            if (!Schema::hasColumn('categories', 'listing_frequency')) {
                $table->unsignedTinyInteger('listing_frequency')
                      ->default(4)
                      ->after('listing_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'listing_frequency')) {
                $table->dropColumn('listing_frequency');
            }
        });
    }
};

