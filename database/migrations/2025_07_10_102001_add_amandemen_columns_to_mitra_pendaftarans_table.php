<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmandemenColumnsToMitraPendaftaransTable extends Migration
{
    public function up()
    {
        Schema::table('mitra_pendaftarans', function (Blueprint $table) {
            $table->string('amd_khs_mitra_3')->nullable()->after('amd_khs_mitra_2');
            $table->string('amd_khs_mitra_4')->nullable()->after('amd_khs_mitra_3');
            $table->string('amd_khs_mitra_5')->nullable()->after('amd_khs_mitra_4');
        });
    }

    public function down()
    {
        Schema::table('mitra_pendaftarans', function (Blueprint $table) {
            $table->dropColumn(['amd_khs_mitra_3', 'amd_khs_mitra_4', 'amd_khs_mitra_5']);
        });
    }
}
