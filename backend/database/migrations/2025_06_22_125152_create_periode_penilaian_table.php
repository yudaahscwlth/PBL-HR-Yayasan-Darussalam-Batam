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
        // PERINGATAN: Ini akan menghapus tabel dan semua datanya!
        Schema::dropIfExists('periode_penilaian');
        
        Schema::create('periode_penilaian', function (Blueprint $table) {
            $table->id(); // BIGINT PRIMARY KEY AUTO_INCREMENT
            $table->string('nama_periode')->comment('Contoh: Semester 1 2025');
            $table->tinyInteger('semester')->nullable()->comment('1 atau 2 (opsional)');
            $table->year('tahun')->comment('Tahun periode, contoh: 2025');
            $table->date('tanggal_mulai')->comment('Tanggal mulai periode');
            $table->date('tanggal_selesai')->comment('Tanggal akhir periode');
            $table->enum('status', ['belum_dibuka', 'aktif', 'selesai'])->default('belum_dibuka');
            $table->timestamps(); // created_at dan updated_at
            
            // Index untuk performa query
            $table->index(['tahun', 'semester']);
            $table->index('status');
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_penilaian');
    }
};