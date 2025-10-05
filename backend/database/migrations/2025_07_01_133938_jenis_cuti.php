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
        Schema::create('jenis_cuti', function (Blueprint $table) {
            $table->id('id_jenis_cuti');
            $table->string('nama_jenis_cuti', 100)->collation('utf8mb4_general_ci');
            $table->text('deskripsi')->nullable()->collation('utf8mb4_general_ci');
            $table->integer('max_hari_cuti')->default(12);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index untuk pencarian
            $table->index('nama_jenis_cuti');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_cuti');
    }
};