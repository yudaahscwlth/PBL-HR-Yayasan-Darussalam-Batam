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
        // Modify nomor_rekening to be nullable
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE profile_pribadi MODIFY nomor_rekening VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_pribadi', function (Blueprint $table) {
            //
        });
    }
};
