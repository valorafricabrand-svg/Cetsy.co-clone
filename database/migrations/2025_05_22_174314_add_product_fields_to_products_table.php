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
            $table->string('product_type')->nullable()->after('price')->comment('physical, digital, service, etc.');
            $table->string('condition')->nullable()->after('product_type')->comment('new, used, refurbished');
            $table->decimal('discount_price', 10, 2)->nullable()->after('price');
            $table->integer('low_stock')->default(5)->after('stock')->comment('threshold for low stock alerts');
            $table->string('download_file')->nullable()->after('description')->comment('for digital products');
            $table->integer('download_limit')->nullable()->after('download_file')->comment('max download attempts');
            $table->dateTime('access_expiry')->nullable()->after('download_limit')->comment('when download access expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'product_type',
                'condition',
                'discount_price',
                'low_stock',
                'download_file',
                'download_limit',
                'access_expiry',
                'variants'
            ]);
            
        });
    }
};
