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
        Schema::create('profile_pekerjaans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('id_departemen')->nullable();
            $table->foreign('id_departemen')->references('id')->on('departemens')->onUpdate('cascade')->onDelete('set null');
            $table->unsignedBigInteger('id_tempat_kerja')->nullable();
            $table->foreign('id_tempat_kerja')->references('id')->on('tempat_kerjas')->onUpdate('cascade')->onDelete('set null');
            $table->unsignedBigInteger('id_jabatan')->nullable();
            $table->foreign('id_jabatan')->references('id')->on('jabatans')->onUpdate('cascade')->onDelete('set null');
            $table->string('nomor_induk_karyawan')->unique()->nullable();
            $table->date('tanggal_masuk');
            $table->enum('status', ['aktif', 'nonaktif', 'kontrak', 'tetap', 'magang', 'honorer', 'pensiun', 'cuti', 'skorsing'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_pekerjaans');
    }
};
