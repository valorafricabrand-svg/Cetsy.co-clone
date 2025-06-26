<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            /*
             |  5 total digits, 4 after the decimal:
             |  - supports values up to 9.9999 %
             |  - default is 3.98 %  → 0.0398
             */
            $table->decimal('paypal_transaction_fee_percent', 5, 4)
                  ->default(0.0398)
                  ->after('id');   // adjust “after” as needed
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('paypal_transaction_fee_percent');
        });
    }
};
