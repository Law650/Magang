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
        Schema::table('log_valves', function (Blueprint $table) {
            $table->string('foto_eviden_2', 255)->nullable()->after('foto_eviden');
            $table->boolean('is_edited')->default(false)->after('foto_eviden_2');
        });

        Schema::table('log_tekanans', function (Blueprint $table) {
            $table->boolean('is_edited')->default(false)->after('foto_eviden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_valves', function (Blueprint $table) {
            $table->dropColumn(['foto_eviden_2', 'is_edited']);
        });

        Schema::table('log_tekanans', function (Blueprint $table) {
            $table->dropColumn('is_edited');
        });
    }
};
