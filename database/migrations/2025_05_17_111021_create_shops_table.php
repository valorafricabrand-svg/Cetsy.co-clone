<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // one-to-one with User
            $table->string('name')->unique();
            $table->string('slug')->unique(); // used for URL
            $table->text('bio')->nullable();
            $table->string('logo')->nullable(); // image path stored via Laravel Filesystem
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
