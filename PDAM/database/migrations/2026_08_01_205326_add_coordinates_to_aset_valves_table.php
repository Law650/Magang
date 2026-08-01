<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('aset_valves', function (Blueprint $table) {
            $table->decimal('latitude', 11, 8)->nullable()->after('total_tutupan_saat_ini');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });

        // Migrate data from lokasis table to aset_valves table
        DB::statement('
            UPDATE aset_valves 
            JOIN lokasis ON aset_valves.lokasi_id = lokasis.id 
            SET aset_valves.latitude = lokasis.latitude, 
                aset_valves.longitude = lokasis.longitude
            WHERE lokasis.jenis = "valve"
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aset_valves', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
