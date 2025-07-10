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
    // 1️⃣  Attribute names per category  (Color, Size, Material …)
    Schema::create('category_attributes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        $table->string('name');                     // e.g. “Color”
        $table->timestamps();
    });

    // 2️⃣  Allowed values for that attribute  (Red, Blue … Small, Large …)
    Schema::create('category_attribute_values', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_attribute_id')
              ->constrained()->cascadeOnDelete();
        $table->string('value');                   // e.g. “Red”
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_attributes_tables');
    }
};
