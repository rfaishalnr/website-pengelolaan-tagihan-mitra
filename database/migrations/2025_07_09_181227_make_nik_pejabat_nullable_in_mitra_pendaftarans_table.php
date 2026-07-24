<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeNikPejabatNullableInMitraPendaftaransTable extends Migration
{
    public function up(): void
    {
        Schema::table('mitra_pendaftarans', function (Blueprint $table) {
            $table->string('nik_pejabat')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mitra_pendaftarans', function (Blueprint $table) {
            $table->string('nik_pejabat')->nullable(false)->change();
        });
    }
}
