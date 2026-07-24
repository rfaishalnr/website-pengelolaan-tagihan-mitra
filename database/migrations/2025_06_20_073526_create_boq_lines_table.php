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
// database/migrations/xxxx_xx_xx_create_boq_lines_table.php
Schema::create('boq_lines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('mitra_pendaftaran_id')->constrained()->onDelete('cascade');
    $table->unsignedInteger('no')->nullable(); // tambahkan ini
    $table->string('nama_lokasi');
    $table->string('sto');

    // NILAI SP
    $table->decimal('sp_material', 15, 2)->nullable();
    $table->decimal('sp_jasa', 15, 2)->nullable();
    $table->decimal('sp_total', 15, 2)->nullable();

    // NILAI REKON
    $table->decimal('rekon_material', 15, 2)->nullable();
    $table->decimal('rekon_jasa', 15, 2)->nullable();
    $table->decimal('rekon_total', 15, 2)->nullable();

    // NILAI TAMBAH
    $table->decimal('tambah_material', 15, 2)->nullable();
    $table->decimal('tambah_jasa', 15, 2)->nullable();
    $table->decimal('tambah_total', 15, 2)->nullable();

    // NILAI KURANG
    $table->decimal('kurang_material', 15, 2)->nullable();
    $table->decimal('kurang_jasa', 15, 2)->nullable();
    $table->decimal('kurang_total', 15, 2)->nullable();

    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boq_lines');
    }
};