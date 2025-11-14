<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->unsignedBigInteger('deal_id')->nullable()->after('sort_order');
            $table->unsignedBigInteger('category_id')->nullable()->after('deal_id');
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropColumn(['deal_id', 'category_id']);
        });
    }
};

