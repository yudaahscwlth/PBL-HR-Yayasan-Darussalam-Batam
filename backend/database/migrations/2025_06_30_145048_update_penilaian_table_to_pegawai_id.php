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
        // Drop the incorrect foreign key constraint
        Schema::table('penilaian', function (Blueprint $table) {
            $table->dropForeign('penilaian_periode_id_foreign');
        });

        // Add the correct foreign key constraint
        Schema::table('penilaian', function (Blueprint $table) {
            $table->foreign('periode_id')
                  ->references('id')
                  ->on('periode_penilaian')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the correct foreign key constraint
        Schema::table('penilaian', function (Blueprint $table) {
            $table->dropForeign(['periode_id']);
        });

        // Restore the incorrect foreign key constraint (for rollback purposes)
        Schema::table('penilaian', function (Blueprint $table) {
            $table->foreign('periode_id')
                  ->references('id')
                  ->on('periode_kuisioner')
                  ->onDelete('cascade');
        });
    }
};