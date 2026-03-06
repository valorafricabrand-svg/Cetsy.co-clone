<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_files', function (Blueprint $table) {
            $table->string('disk')->nullable()->after('filepath');
            $table->unsignedBigInteger('filesize')->nullable()->after('disk');
            $table->string('filetype')->nullable()->after('filesize');
            $table->string('source_type')->default('upload')->after('filetype');
            $table->text('external_url')->nullable()->after('source_type');
            $table->string('filepath')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('digital_files')
            ->whereNull('filepath')
            ->update(['filepath' => '']);

        Schema::table('digital_files', function (Blueprint $table) {
            $table->string('filepath')->nullable(false)->change();
            $table->dropColumn([
                'disk',
                'filesize',
                'filetype',
                'source_type',
                'external_url',
            ]);
        });
    }
};
