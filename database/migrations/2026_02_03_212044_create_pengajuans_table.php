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
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke User (Mitra)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Kolom Data 
            $table->string('nomor_sp');
            $table->string('nama_pekerjaan');
            
            // Kolom Validasi
            $table->enum('status', ['pending', 'acc', 'tolak'])->default('pending');
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
