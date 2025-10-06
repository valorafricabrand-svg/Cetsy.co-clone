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
        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'causer_type')) {
                $table->string('causer_type')->nullable();
            }
            if (!Schema::hasColumn('activities', 'causer_id')) {
                $table->unsignedBigInteger('causer_id')->nullable();
            }
            if (!Schema::hasColumn('activities', 'subject_type')) {
                $table->string('subject_type')->nullable();
            }
            if (!Schema::hasColumn('activities', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            if (Schema::hasColumn('activities', 'subject_id')) {
                $table->dropColumn('subject_id');
            }
            if (Schema::hasColumn('activities', 'subject_type')) {
                $table->dropColumn('subject_type');
            }
            if (Schema::hasColumn('activities', 'causer_id')) {
                $table->dropColumn('causer_id');
            }
            if (Schema::hasColumn('activities', 'causer_type')) {
                $table->dropColumn('causer_type');
            }
        });
    }
};

