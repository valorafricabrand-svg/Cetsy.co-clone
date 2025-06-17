<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();

            /* store the product and viewer IDs, BUT no foreign-key clauses */
            $table->unsignedBigInteger('product_id');   // or ->uuid() if products.id is UUID
            $table->unsignedBigInteger('viewer_id')->nullable();  // or ->uuid()->nullable()

            $table->ipAddress('ip')->nullable();
            $table->timestamps();

            /* helpful indexes (optional but recommended) */
            $table->index('product_id');
            $table->index('viewer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};
