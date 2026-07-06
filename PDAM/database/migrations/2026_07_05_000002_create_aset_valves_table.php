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
        Schema::create('aset_valves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokasi_id')->constrained('lokasis')->cascadeOnDelete();
            $table->string('nama_aset', 150);
            $table->decimal('kapasitas_full_putaran', 8, 2);
            $table->decimal('total_tutupan_saat_ini', 8, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();

            $table->index('lokasi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset_valves');
    }
};
