<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Core site settings
            $table->string('site_name')->default('')->after('id');
            $table->string('meta_description')->default('')->after('site_name');

            // Contact info
            $table->string('phone')->default('')->after('meta_description');
            $table->string('email')->default('')->after('phone');

            // Social URLs
            $table->string('facebook_url')->default('')->after('email');
            $table->string('instagram_url')->default('')->after('facebook_url');
            $table->string('x_url')->default('')->after('instagram_url');           // formerly Twitter
            $table->string('linkedin_url')->default('')->after('x_url');
            $table->string('tiktok_url')->default('')->after('linkedin_url');

            // Payment & currency
            $table->string('paypal_client_id')->default('')->after('tiktok_url');
            $table->string('default_currency')->default(config('app.currency'))->after('paypal_client_id');

            // Additional common defaults
            $table->string('youtube_url')->default('')->after('default_currency');
            $table->string('whatsapp_number')->default('')->after('youtube_url');
            $table->string('address')->nullable()->default('')->after('whatsapp_number');
            $table->string('timezone')->default(config('app.timezone'))->after('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'site_name',
                'meta_description',
                'phone',
                'email',
                'facebook_url',
                'instagram_url',
                'x_url',
                'linkedin_url',
                'tiktok_url',
                'paypal_client_id',
                'default_currency',
                'youtube_url',
                'whatsapp_number',
                'address',
                'timezone',
            ]);
        });
    }
}
