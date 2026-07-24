<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('mitra_pendaftarans', function (Blueprint $table) {
            $table->decimal('ppn_percent', 5, 2)->default(11);
        });
    }
    
    public function down()
    {
        Schema::table('mitra_pendaftaran', function (Blueprint $table) {
            $table->dropColumn('ppn_percent');
        });
    }
    
};
