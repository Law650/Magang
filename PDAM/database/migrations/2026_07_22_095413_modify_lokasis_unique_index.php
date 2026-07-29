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
        Schema::table('lokasis', function (Blueprint $table) {
            $table->dropUnique('lokasis_nama_lokasi_unique');
            $table->unique(['nama_lokasi', 'jenis'], 'lokasis_nama_lokasi_jenis_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lokasis', function (Blueprint $table) {
            $table->dropUnique('lokasis_nama_lokasi_jenis_unique');
            $table->unique('nama_lokasi', 'lokasis_nama_lokasi_unique');
        });
    }
};
