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
        Schema::create('evidence_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appeal_id')->constrained('appeals')->onDelete('cascade');
            $table->foreignId('dispute_id')->constrained('disputes')->onDelete('cascade');
            $table->foreignId('requested_from')->constrained('users')->onDelete('cascade'); // Who is being asked for evidence
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade'); // Who is requesting (system/appeal)
            $table->text('message');
            $table->enum('status', ['pending', 'responded', 'overdue', 'closed'])->default('pending');
            $table->timestamp('deadline')->nullable(); // When evidence should be submitted by
            $table->timestamp('responded_at')->nullable(); // When evidence was submitted
            $table->json('required_evidence_types')->nullable(); // What types of evidence are needed
            $table->text('response_notes')->nullable(); // Notes from the user's response
            $table->json('submitted_evidence')->nullable(); // Evidence submitted in response
            $table->timestamps();
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence_requests');
    }
};
