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
        Schema::create('profile_pribadi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('nomor_induk_kependudukan')->unique()->nullable();
            $table->string('nama_lengkap')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin',['pria','wanita'])->nullable();
            $table->enum('golongan_darah',['a','b','ab','o'])->nullable();
            $table->enum('status_pernikahan',['belum nikah','sudah nikah'])->nullable();
            $table->string('npwp')->unique()->nullable();
            $table->string('kecamatan')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
