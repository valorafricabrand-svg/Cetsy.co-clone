<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Minimal change: add only the JSON `properties` column used for product update diffs
        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'properties')) {
                $afterColumn = Schema::hasColumn('activities', 'related_type') ? 'related_type' : 'description';
                $table->json('properties')->nullable()->after($afterColumn);
            }
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $drop = function(string $col) use ($table) {
                if (Schema::hasColumn('activities', $col)) {
                    $table->dropColumn($col);
                }
            };
            $drop('properties');
        });
    }
};
