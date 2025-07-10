<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_option_id');  // links back to product_options
            $table->string('value');                          // e.g. "Red", "M"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_values');
    }
};
