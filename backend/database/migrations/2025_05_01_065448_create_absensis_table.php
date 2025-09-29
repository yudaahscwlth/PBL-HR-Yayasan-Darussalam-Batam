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
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->date('tanggal');
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->decimal('latitude_in', 18, 15)->nullable();
            $table->decimal('longitude_in', 18, 15)->nullable();
            $table->decimal('latitude_out', 18, 15)->nullable();
            $table->decimal('longitude_out', 18, 15)->nullable();
            $table->enum('status',['hadir','sakit','cuti','terlambat','alpa']);
            $table->text('keterangan')->nullable();
            $table->string('file_pendukung')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
