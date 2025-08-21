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
        Schema::create('appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained('disputes')->onDelete('cascade');
            $table->foreignId('appealed_by')->constrained('users')->onDelete('cascade');
            $table->text('reason');
            $table->json('new_evidence')->nullable(); // Store new evidence for appeal
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected']);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->enum('decision', ['approved', 'rejected'])->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['dispute_id', 'status']);
            $table->index(['appealed_by', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appeals');
    }
};
