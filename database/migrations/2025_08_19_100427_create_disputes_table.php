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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['customs_fees', 'item_misrepresentation', 'shipping_issues', 'quality_issues', 'payment_issues', 'other']);
            $table->enum('status', ['pending', 'under_review', 'resolved', 'appealed', 'appeal_under_review', 'final']);
            $table->text('description');
            $table->json('evidence')->nullable(); // Store evidence files, screenshots, etc.
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('appeal_deadline')->nullable();
            $table->boolean('can_appeal')->default(true);
            $table->enum('decision', ['buyer_wins', 'seller_wins', 'partial_refund', 'no_action'])->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['order_id', 'status']);
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index('appeal_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
