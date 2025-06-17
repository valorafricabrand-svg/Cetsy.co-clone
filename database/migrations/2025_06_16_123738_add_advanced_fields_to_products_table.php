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
            // Add the new columns first
            $table->boolean('renewal_option')->default(0);
            $table->unsignedBigInteger('listTypeFee_id')->nullable();
            $table->string('variation_one_name')->nullable();
            $table->string('variation_two_name')->nullable();
            $table->unsignedBigInteger('origin_id')->nullable();
            $table->string('origin_postal_code', 50)->nullable();
            $table->unsignedBigInteger('processing_time_id')->nullable();
            $table->unsignedBigInteger('local_shipping_service_id')->nullable();
            $table->string('local_shipping_service_other')->nullable();
            $table->unsignedBigInteger('localshippingPeriod_id')->nullable();
            $table->decimal('local_default_shipping_price', 10, 2)->nullable();
            $table->decimal('local_shipping_price', 10, 2)->nullable();
            $table->integer('shipping_type')->nullable();
            $table->unsignedBigInteger('international_shipping_service_id')->nullable();
            $table->string('international_shipping_service_other')->nullable();
            $table->unsignedBigInteger('internationalshippingPeriod_id')->nullable();
            $table->decimal('default_shipping_price', 10, 2)->nullable();
            $table->decimal('shipping_price', 10, 2)->nullable();
            $table->integer('shipping_type_other')->nullable();
            $table->boolean('item_return')->nullable();
            $table->boolean('item_exchange')->nullable();
            $table->integer('total_return_days')->nullable();

            // Add foreign key constraints
            $table->foreign('listTypeFee_id')->references('id')->on('listing_fee_types')->onDelete('set null');
            $table->foreign('origin_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('processing_time_id')->references('id')->on('processing_times')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop columns only if they exist
            if (Schema::hasColumn('products', 'product_type')) {
                $table->dropColumn('product_type');
            }

            if (Schema::hasColumn('products', 'condition')) {
                $table->dropColumn('condition');
            }

            if (Schema::hasColumn('products', 'discount_price')) {
                $table->dropColumn('discount_price');
            }

            if (Schema::hasColumn('products', 'low_stock')) {
                $table->dropColumn('low_stock');
            }

            if (Schema::hasColumn('products', 'download_file')) {
                $table->dropColumn('download_file');
            }

            if (Schema::hasColumn('products', 'download_limit')) {
                $table->dropColumn('download_limit');
            }

            if (Schema::hasColumn('products', 'access_expiry')) {
                $table->dropColumn('access_expiry');
            }

            if (Schema::hasColumn('products', 'variants')) {
                $table->dropColumn('variants');
            }

            // Drop the added foreign key constraints
            $table->dropForeign(['listTypeFee_id']);
            $table->dropForeign(['origin_id']);
            $table->dropForeign(['processing_time_id']);

            // Drop the newly added columns
            $table->dropColumn('renewal_option');
            $table->dropColumn('listTypeFee_id');
            $table->dropColumn('variation_one_name');
            $table->dropColumn('variation_two_name');
            $table->dropColumn('origin_id');
            $table->dropColumn('origin_postal_code');
            $table->dropColumn('processing_time_id');
            $table->dropColumn('local_shipping_service_id');
            $table->dropColumn('local_shipping_service_other');
            $table->dropColumn('localshippingPeriod_id');
            $table->dropColumn('local_default_shipping_price');
            $table->dropColumn('local_shipping_price');
            $table->dropColumn('shipping_type');
            $table->dropColumn('international_shipping_service_id');
            $table->dropColumn('international_shipping_service_other');
            $table->dropColumn('internationalshippingPeriod_id');
            $table->dropColumn('default_shipping_price');
            $table->dropColumn('shipping_price');
            $table->dropColumn('shipping_type_other');
            $table->dropColumn('item_return');
            $table->dropColumn('item_exchange');
            $table->dropColumn('total_return_days');
        });
    }
};
