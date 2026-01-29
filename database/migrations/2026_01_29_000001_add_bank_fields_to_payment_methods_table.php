<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_methods', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('account_name');
            }
            if (!Schema::hasColumn('payment_methods', 'bank_country')) {
                $table->string('bank_country', 3)->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('payment_methods', 'bank_currency')) {
                $table->string('bank_currency', 3)->nullable()->after('bank_country');
            }
            if (!Schema::hasColumn('payment_methods', 'bank_routing_number')) {
                $table->string('bank_routing_number')->nullable()->after('bank_currency');
            }
            if (!Schema::hasColumn('payment_methods', 'swift_bic')) {
                $table->string('swift_bic', 32)->nullable()->after('bank_routing_number');
            }
            if (!Schema::hasColumn('payment_methods', 'iban')) {
                $table->string('iban', 64)->nullable()->after('swift_bic');
            }
            if (!Schema::hasColumn('payment_methods', 'bank_address')) {
                $table->string('bank_address')->nullable()->after('iban');
            }
            if (!Schema::hasColumn('payment_methods', 'wise_email')) {
                $table->string('wise_email')->nullable()->after('bank_address');
            }
            if (!Schema::hasColumn('payment_methods', 'wise_recipient_id')) {
                $table->string('wise_recipient_id')->nullable()->after('wise_email');
            }
            if (!Schema::hasColumn('payment_methods', 'wise_profile_id')) {
                $table->string('wise_profile_id')->nullable()->after('wise_recipient_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $columns = [
                'wise_profile_id',
                'wise_recipient_id',
                'wise_email',
                'bank_address',
                'iban',
                'swift_bic',
                'bank_routing_number',
                'bank_currency',
                'bank_country',
                'bank_name',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('payment_methods', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
