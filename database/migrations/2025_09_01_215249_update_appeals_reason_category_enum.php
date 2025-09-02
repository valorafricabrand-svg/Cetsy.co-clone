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
        // First, we need to drop the existing enum column and recreate it with new values
        // This is because MySQL doesn't support altering enum values directly
        Schema::table('appeals', function (Blueprint $table) {
            $table->dropColumn('reason_category');
        });
        
        Schema::table('appeals', function (Blueprint $table) {
            $table->enum('reason_category', [
                'new_evidence', 
                'procedural_error', 
                'decision_error', 
                'review_concerns', 
                'seller_unresponsive', 
                'urgent_review', 
                'other'
            ])->after('reason')->nullable()->comment('Category of the appeal reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original enum values
        Schema::table('appeals', function (Blueprint $table) {
            $table->dropColumn('reason_category');
        });
        
        Schema::table('appeals', function (Blueprint $table) {
            $table->enum('reason_category', [
                'new_evidence', 
                'procedural_error', 
                'decision_error', 
                'other'
            ])->after('reason')->nullable()->comment('Category of the appeal reason');
        });
    }
};
