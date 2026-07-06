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
        Schema::create('log_valves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_valve_id')->constrained('aset_valves')->cascadeOnDelete();
            $table->string('nama_teknisi', 150);
            $table->dateTime('waktu_kegiatan');
            $table->enum('aksi_kerja', ['buka', 'tutup']);
            $table->decimal('jumlah_putaran', 6, 2);
            $table->text('keterangan')->nullable();
            $table->decimal('snapshot_sisa_bukaan', 8, 2);
            $table->decimal('snapshot_total_tutupan', 8, 2);
            $table->timestamps();

            $table->index('aset_valve_id');
            $table->index('aksi_kerja');
            $table->index('waktu_kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_valves');
    }
};
