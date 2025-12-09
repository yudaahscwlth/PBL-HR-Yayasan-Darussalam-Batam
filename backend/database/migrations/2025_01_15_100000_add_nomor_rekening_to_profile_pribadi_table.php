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
        if (Schema::hasTable('profile_pribadi')) {
            if (!Schema::hasColumn('profile_pribadi', 'nomor_rekening')) {
                Schema::table('profile_pribadi', function (Blueprint $table) {
                    $table->string('nomor_rekening', 255)->nullable()->after('no_hp');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_pribadi', function (Blueprint $table) {
            if (Schema::hasColumn('profile_pribadi', 'nomor_rekening')) {
                $table->dropColumn('nomor_rekening');
            }
        });
    }
};

