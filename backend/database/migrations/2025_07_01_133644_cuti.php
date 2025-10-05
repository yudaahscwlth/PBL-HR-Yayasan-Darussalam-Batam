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
        Schema::create('cuti', function (Blueprint $table) {
            $table->id('id_cuti');
            $table->Integer('id_pegawai'); // INT UNSIGNED, sama seperti pegawai.id_pegawai
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->unsignedBigInteger('id_jenis_cuti');
            $table->enum('status_cuti', ['Disetujui', 'Ditolak', 'Menunggu'])
                  ->default('Menunggu')
                  ->collation('utf8mb4_general_ci');
            $table->text('keterangan')
                  ->nullable()
                  ->collation('utf8mb4_general_ci');

            // Foreign key constraints
            $table->foreign('id_pegawai')
                  ->references('id_pegawai')
                  ->on('pegawai')
                  ->onDelete('cascade');

            $table->foreign('id_jenis_cuti')
                  ->references('id_jenis_cuti')
                  ->on('jenis_cuti')
                  ->onDelete('cascade');

            // Indexes for better performance
            $table->index('id_pegawai');
            $table->index('id_jenis_cuti');
            $table->index('status_cuti');
            $table->index('tanggal_pengajuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuti');
    }
};
