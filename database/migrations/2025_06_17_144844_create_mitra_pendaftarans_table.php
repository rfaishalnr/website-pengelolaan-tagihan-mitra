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
        Schema::create('mitra_pendaftarans', function (Blueprint $table) {
            $table->id();
            // Step 1
            $table->string('nama_mitra');
            $table->string('no_khs_mitra');
            $table->string('amd_khs_mitra_1')->nullable();
            $table->string('amd_khs_mitra_2')->nullable();
            $table->string('nomer_sp_mitra');
            $table->string('amd_sp');
            $table->string('nama_pekerjaan');
            $table->string('regional');
            $table->string('area');
            $table->string('idp');
            $table->date('toc');
            $table->text('alamat_kantor');
            
            // Step 2
            $table->string('nama_pejabat_ta');
            $table->string('posisi_pejabat_ta');
            $table->string('nik_pejabat');
            $table->string('nama_mgr_area');
            $table->string('jabatan_mgr_area');
            $table->string('direktur_mitra');
            $table->string('jabatan_mitra');
            $table->string('waspang');
            $table->string('jabatan_waspang');
            $table->string('nik_waspang');
        
            // Step 3
            $table->string('no_baut');
            $table->date('tanggal_baut');
        
            // Step 4
            $table->string('no_ba_rekon');
            $table->date('tanggal_ba_rekon');
        
            // Step 5
            $table->string('no_ba_abd');
            $table->date('tanggal_ba_abd');
        
            // Step 6
            // $table->string('no_ba_legal')->nullable();
            $table->date('tanggal_ba_legal');
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_pendaftarans');
    }
};
