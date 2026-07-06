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
        Schema::create('log_tekanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokasi_id')->constrained('lokasis')->cascadeOnDelete();
            $table->decimal('nilai_tekanan', 5, 2);
            $table->enum('status', ['normal', 'rendah', 'kritis']);
            $table->dateTime('waktu_pengecekan');
            $table->timestamps();

            $table->index('lokasi_id');
            $table->index('status');
            $table->index('waktu_pengecekan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_tekanans');
    }
};
