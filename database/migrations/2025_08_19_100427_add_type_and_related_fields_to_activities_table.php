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
            $table->string('type')->default('general')->after('description');
            $table->unsignedBigInteger('related_id')->nullable()->after('type');
            $table->string('related_type')->nullable()->after('related_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('related_id');
            $table->dropColumn('related_type');
        });
    }
};
