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
    Schema::table('products', function (Blueprint $table) {
        $table->string('weight', 50)->nullable();
        $table->string('length', 50)->nullable();
        $table->string('width', 50)->nullable();
        $table->string('height', 50)->nullable();
        $table->string('shipping_class', 100)->nullable();
        $table->boolean('requires_shipping')->default(false);
    });
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn(['weight','length','width','height','shipping_class','requires_shipping']);
    });
}

};
