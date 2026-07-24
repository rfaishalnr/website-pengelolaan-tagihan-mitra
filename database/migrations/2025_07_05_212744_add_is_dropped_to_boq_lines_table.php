<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('boq_lines', function (Blueprint $table) {
            $table->boolean('is_dropped')->default(false)->after('kurang_total');
        });
    }

    public function down(): void
    {
        Schema::table('boq_lines', function (Blueprint $table) {
            $table->dropColumn('is_dropped');
        });
    }
};
