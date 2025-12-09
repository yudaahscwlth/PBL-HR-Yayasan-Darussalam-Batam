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
        if (Schema::hasTable('slip_gaji')) {
            if (Schema::hasColumn('slip_gaji', 'nomor_rekening')) {
                Schema::table('slip_gaji', function (Blueprint $table) {
                    $table->dropColumn('nomor_rekening');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('slip_gaji')) {
            if (!Schema::hasColumn('slip_gaji', 'nomor_rekening')) {
                Schema::table('slip_gaji', function (Blueprint $table) {
                    $table->string('nomor_rekening', 255)->nullable()->after('total_gaji');
                });
            }
        }
    }
};
