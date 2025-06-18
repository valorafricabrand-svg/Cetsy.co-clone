<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('digital_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('filepath');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('digital_files');
    }
};

