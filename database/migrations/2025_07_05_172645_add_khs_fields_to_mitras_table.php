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
        Schema::table('mitras', function (Blueprint $table) {
            $table->string('no_khs_mitra')->nullable()->after('nama_mitra');
            $table->string('amd_khs_mitra_1')->nullable()->after('no_khs_mitra');
            $table->string('amd_khs_mitra_2')->nullable()->after('amd_khs_mitra_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitras', function (Blueprint $table) {
            $table->dropColumn([
                'no_khs_mitra',
                'amd_khs_mitra_1',
                'amd_khs_mitra_2'
            ]);
        });
    }
};