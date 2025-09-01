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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // buyer or seller
            $table->enum('party_type', ['buyer', 'seller']);
            $table->text('request_message'); // Cetsy team's request message
            $table->json('required_evidence_types')->nullable(); // Types of evidence requested
            $table->timestamp('deadline'); // When evidence must be submitted by
            $table->json('submitted_evidence')->nullable(); // Evidence submitted by user
            $table->timestamp('submitted_at')->nullable(); // When evidence was submitted
            $table->enum('status', ['pending', 'submitted', 'overdue', 'reviewed'])->default('pending');
            $table->text('admin_notes')->nullable(); // Admin notes about the evidence
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
