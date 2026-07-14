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
        Schema::table('log_tekanans', function (Blueprint $table) {
            $table->string('status_aliran')->nullable()->after('status');
            $table->string('kekeruhan')->nullable()->after('status_aliran');
            $table->text('keterangan')->nullable()->after('kekeruhan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_tekanans', function (Blueprint $table) {
            $table->dropColumn(['status_aliran', 'kekeruhan', 'keterangan']);
        });
    }
};
