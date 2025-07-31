<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariationTypesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('variation_types', function (Blueprint $table) {
            $table->id();
            // link each variation type to its product
            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // the name of this variation type, e.g. "Length" or "Color"
            $table->string('name');

            // if you want to allow linking photos per option
            $table->boolean('link_photos')
                  ->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variation_types');
    }
}
