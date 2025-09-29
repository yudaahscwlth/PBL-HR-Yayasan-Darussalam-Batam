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
        Schema::create('pengajuan_cutis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('tipe_cuti',['cuti tahunan','cuti melahirkan','cuti nikah','cuti kematian','cuti bersama','cuti pemotongan gaji','cuti lainnya']);
            $table->enum('status_pengajuan', [
                'ditinjau kepala sekolah',
                'disetujui kepala sekolah',
                'disetujui kepala sekolah menunggu tinjauan dirpen',
                'ditolak kepala sekolah',
                'ditinjau hrd',
                'disetujui hrd',
                'disetujui hrd menunggu tinjauan dirpen',
                'ditolak hrd',
                'ditinjau kepala hrd',
                'disetujui kepala hrd',
                'disetujui kepala hrd menunggu tinjauan dirpen',
                'ditolak kepala hrd',
                'ditinjau dirpen',
                'disetujui dirpen',
                'ditolak dirpen',
            ])->default('ditinjau kepala sekolah');

            $table->text('alasan_pendukung')->nullable();
            $table->string('file_pendukung')->nullable();
            $table->text('komentar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cutis');
    }
};
