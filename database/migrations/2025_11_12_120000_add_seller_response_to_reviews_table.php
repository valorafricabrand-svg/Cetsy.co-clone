<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'seller_response')) {
                $table->text('seller_response')->nullable()->after('comment');
            }
            if (!Schema::hasColumn('reviews', 'seller_responded_at')) {
                $table->timestamp('seller_responded_at')->nullable()->after('seller_response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'seller_responded_at')) {
                $table->dropColumn('seller_responded_at');
            }
            if (Schema::hasColumn('reviews', 'seller_response')) {
                $table->dropColumn('seller_response');
            }
        });
    }
};

