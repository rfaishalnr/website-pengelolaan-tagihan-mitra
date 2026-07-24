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
        Schema::table('pejabat_histories', function (Blueprint $table) {
            $table->string('posisi')->after('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pejabat_histories', function (Blueprint $table) {
            $table->dropColumn('posisi');
        });
    }
};