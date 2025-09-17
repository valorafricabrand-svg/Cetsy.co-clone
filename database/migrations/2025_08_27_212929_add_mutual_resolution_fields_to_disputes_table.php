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
            if (!Schema::hasColumn('disputes', 'mutual_resolution_terms')) {
                $table->text('mutual_resolution_terms')->nullable()->after('admin_notes');
            }

            if (!Schema::hasColumn('disputes', 'buyer_agreed_at')) {
                $table->timestamp('buyer_agreed_at')->nullable()->after('mutual_resolution_terms');
            }

            if (!Schema::hasColumn('disputes', 'seller_agreed_at')) {
                $table->timestamp('seller_agreed_at')->nullable()->after('buyer_agreed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $columns = [];

            foreach (['seller_agreed_at', 'buyer_agreed_at', 'mutual_resolution_terms'] as $column) {
                if (Schema::hasColumn('disputes', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
