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
        Schema::table('mitra_pendaftarans', function (Blueprint $table) {
            $table->string('waspang_mitra')->nullable()->after('nik_waspang');
            $table->string('jabatan_waspang_mitra')->nullable()->after('waspang_mitra');
            $table->string('nik_waspang_mitra')->nullable()->after('jabatan_waspang_mitra');
            $table->string('periode_waspang_mitra')->nullable()->after('nik_waspang_mitra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra_pendaftarans', function (Blueprint $table) {
            $table->dropColumn([
                'waspang_mitra',
                'jabatan_waspang_mitra',
                'nik_waspang_mitra',
                'periode_waspang_mitra',
            ]);
        });
    }
};