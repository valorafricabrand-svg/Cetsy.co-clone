<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Add category_id column, nullable for now to avoid issues if you have existing products
            $table->unsignedBigInteger('category_id')->nullable()->after('id');

            // Add foreign key constraint
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['category_id']);
            
            // Drop the category_id column
            $table->dropColumn('category_id');
        });
    }
};
