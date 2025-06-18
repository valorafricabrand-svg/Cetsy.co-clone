<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipping_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g. "Standard Kenya Delivery"
            $table->string('country')->default('KE');
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->integer('delivery_days')->default(3);
            $table->boolean('pickup_available')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipping_profiles');
    }
};
