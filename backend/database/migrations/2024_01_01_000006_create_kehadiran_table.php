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
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id('id_kehadiran');
            $table->unsignedBigInteger('id_pegawai');
            $table->date('tanggal');
            $table->unsignedBigInteger('lokasi_kantor_id');
            $table->dateTime('waktu_masuk');
            $table->timestamp('waktu_pulang')->nullable();
            $table->enum('status_kehadiran', ['Hadir', 'Tidak Hadir', 'Terlambat', 'Sakit', 'Izin']);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_pegawai')->references('id_pegawai')->on('pegawai')->onDelete('cascade');
            $table->foreign('lokasi_kantor_id')->references('id')->on('lokasi_kantor')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};

