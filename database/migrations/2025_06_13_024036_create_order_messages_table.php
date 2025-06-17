<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('order_messages', function (Blueprint $table) {
            $table->id();

            // Just store the IDs—no foreign keys
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');

            $table->text('body');
            $table->timestamps();

            // If you want indexes for faster lookups:
            $table->index('order_id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_messages');
    }
}
