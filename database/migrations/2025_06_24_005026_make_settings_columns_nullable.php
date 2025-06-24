<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeSettingsColumnsNullable extends Migration
{
    /**
     * Run the migrations – make every field nullable.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            // Core site settings
            $table->string('site_name')->nullable()->change();
            $table->string('meta_description')->nullable()->change();

            // Contact info
            $table->string('phone')->nullable()->change();
            $table->string('email')->nullable()->change();

            // Social URLs
            $table->string('facebook_url')->nullable()->change();
            $table->string('instagram_url')->nullable()->change();
            $table->string('x_url')->nullable()->change();
            $table->string('linkedin_url')->nullable()->change();
            $table->string('tiktok_url')->nullable()->change();
            $table->string('youtube_url')->nullable()->change();

            // Payment & currency
            $table->string('paypal_client_id')->nullable()->change();
            $table->string('default_currency')->nullable()->change();

            // Misc
            $table->string('whatsapp_number')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('timezone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations – revert columns to NOT NULL with empty-string defaults.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            // Core site settings
            $table->string('site_name')->default('')->nullable(false)->change();
            $table->string('meta_description')->default('')->nullable(false)->change();

            // Contact info
            $table->string('phone')->default('')->nullable(false)->change();
            $table->string('email')->default('')->nullable(false)->change();

            // Social URLs
            $table->string('facebook_url')->default('')->nullable(false)->change();
            $table->string('instagram_url')->default('')->nullable(false)->change();
            $table->string('x_url')->default('')->nullable(false)->change();
            $table->string('linkedin_url')->default('')->nullable(false)->change();
            $table->string('tiktok_url')->default('')->nullable(false)->change();
            $table->string('youtube_url')->default('')->nullable(false)->change();

            // Payment & currency
            $table->string('paypal_client_id')->default('')->nullable(false)->change();
            $table->string('default_currency')->default('USD')->nullable(false)->change();

            // Misc
            $table->string('whatsapp_number')->default('')->nullable(false)->change();
            $table->string('address')->default('')->nullable(false)->change();
            $table->string('timezone')->default(config('app.timezone'))->nullable(false)->change();
        });
    }
}
