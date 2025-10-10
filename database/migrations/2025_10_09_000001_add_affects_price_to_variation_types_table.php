<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variation_types', function (Blueprint $table) {
            if (!Schema::hasColumn('variation_types', 'affects_price')) {
                $table->boolean('affects_price')->default(false)->after('link_photos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('variation_types', function (Blueprint $table) {
            if (Schema::hasColumn('variation_types', 'affects_price')) {
                $table->dropColumn('affects_price');
            }
        });
    }
};

