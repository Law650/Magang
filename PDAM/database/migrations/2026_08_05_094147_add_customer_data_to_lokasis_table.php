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
            $table->string('no_sr')->nullable()->unique()->after('id');
            $table->string('nama_pelanggan')->nullable()->after('no_sr');
            $table->text('alamat')->nullable()->after('nama_pelanggan');
            $table->string('desa')->nullable()->after('alamat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lokasis', function (Blueprint $table) {
            $table->dropColumn(['no_sr', 'nama_pelanggan', 'alamat', 'desa']);
        });
    }
};
