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
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id('id_pegawai');
            $table->string('nama', 255);
            $table->string('tempat_lahir', 255);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat');
            $table->enum('status', ['Aktif', 'Nonaktif', 'Cuti'])->default('Aktif');
            $table->string('no_hp', 20);
            $table->string('email', 255);
            $table->unsignedBigInteger('id_jabatan')->nullable();
            $table->unsignedBigInteger('id_departemen')->nullable();
            $table->date('tanggal_masuk');
            $table->string('foto', 255);
            $table->integer('jatahtahunan')->default(0);
            
            // Foreign keys
            $table->foreign('id_jabatan')->references('id_jabatan')->on('jabatan')->onDelete('set null');
            $table->foreign('id_departemen')->references('id_departemen')->on('departemen')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};

