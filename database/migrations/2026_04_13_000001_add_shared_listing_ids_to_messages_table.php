<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'shared_listing_ids')) {
                $table->json('shared_listing_ids')->nullable()->after('attachment_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'shared_listing_ids')) {
                $table->dropColumn('shared_listing_ids');
            }
        });
    }
};
