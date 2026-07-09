<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alignment kolom log_valves & log_tekanans dengan PRD §3.2.
 *
 * Menambahkan: user_id, latitude, longitude, foto_eviden
 * pada kedua tabel log agar sesuai spesifikasi dokumen PRD.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_valves', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('aset_valve_id')
                  ->constrained('users')->nullOnDelete();
            $table->decimal('latitude', 10, 6)->nullable()->after('snapshot_total_tutupan');
            $table->decimal('longitude', 10, 6)->nullable()->after('latitude');
            $table->string('foto_eviden', 255)->nullable()->after('longitude');
        });

        Schema::table('log_tekanans', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('lokasi_id')
                  ->constrained('users')->nullOnDelete();
            $table->string('nama_teknisi', 150)->nullable()->after('user_id');
            $table->decimal('latitude', 10, 6)->nullable()->after('waktu_pengecekan');
            $table->decimal('longitude', 10, 6)->nullable()->after('latitude');
            $table->string('foto_eviden', 255)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('log_valves', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'latitude', 'longitude', 'foto_eviden']);
        });

        Schema::table('log_tekanans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'nama_teknisi', 'latitude', 'longitude', 'foto_eviden']);
        });
    }
};
