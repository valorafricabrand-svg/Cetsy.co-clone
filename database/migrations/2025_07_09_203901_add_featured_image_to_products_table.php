<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the featured_image column.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // If you already have an "image" or "thumbnail" column,
            // placing featured_image right after it keeps things tidy.
            $table->string('featured_image')
                  ->nullable()
                  ->after('id');  // change `image` to any existing column you prefer
        });
    }

    /**
     * Roll back the change.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('featured_image');
        });
    }
};
