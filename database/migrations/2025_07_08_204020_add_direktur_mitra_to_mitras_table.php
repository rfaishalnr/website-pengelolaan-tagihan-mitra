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
            $table->string('direktur_mitra')->nullable();
            $table->string('jabatan_mitra')->nullable();
            $table->text('alamat_kantor')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitras', function (Blueprint $table) {
            $table->dropColumn([
                'direktur_mitra',
                'jabatan_mitra',
                'alamat_kantor',
            ]);
        });
    }
};
