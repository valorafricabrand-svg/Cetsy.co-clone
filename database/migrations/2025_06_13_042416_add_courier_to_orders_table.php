<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            /*
             | We place the new columns near existing shipping info
             | for logical grouping. Adjust position if you prefer.
             */
            $table->string('courier', 100)
                  ->nullable()
                  ->after('id');

            $table->string('tracking_no', 120)
                  ->nullable()
                  ->after('courier');

            $table->timestamp('shipped_at')
                  ->nullable()
                  ->after('tracking_no');

            // Optional free-text note the seller can save from the modal
            $table->text('ship_notes')
                  ->nullable()
                  ->after('tracking_no');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['courier', 'tracking_no', 'shipped_at', 'ship_notes']);
        });
    }
};
