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

            $table->text('body'); // Message content
            $table->json('attachments')->nullable(); // Added attachments column
            $table->string('type')->default('buyer_message'); // Added type column
            $table->timestamps();

            // If you want indexes for faster lookups:
            $table->index('order_id');
            $table->index('user_id');
            $table->index('type'); // Added index for type
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_messages');
    }
}
