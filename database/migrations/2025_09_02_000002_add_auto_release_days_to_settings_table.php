<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'auto_release_days')) {
                $table->unsignedSmallInteger('auto_release_days')->default(3)->after('min_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'auto_release_days')) {
                $table->dropColumn('auto_release_days');
            }
        });
    }
};

