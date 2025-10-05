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
        Schema::create('jawaban_kuisioner', function (Blueprint $table) {
            $table->id(); // BIGINT PRIMARY KEY AUTO_INCREMENT
            
            // Foreign Keys
            $table->foreignId('penilaian_id')
                  ->constrained('penilaian')
                  ->onDelete('cascade')
                  ->comment('FK ke penilaian.id');
                  
            $table->foreignId('kuisioner_id')
                  ->constrained('kuisioner')
                  ->onDelete('cascade')
                  ->comment('FK ke kuisioner.id');
            
            // Data Detail Penilaian
            $table->tinyInteger('skor')
                  ->comment('Skor dari penilai, contoh: 1-5');
                  
            $table->text('komentar')
                  ->nullable()
                  ->comment('Komentar per pertanyaan (optional)');
            
            $table->timestamps(); // created_at dan updated_at
            
            // Indexes untuk performa query
            $table->index('penilaian_id');
            $table->index('kuisioner_id');
            $table->index('skor');
            
            // Unique constraint untuk mencegah duplikasi
            // Satu kuisioner hanya bisa dijawab sekali per penilaian
            $table->unique(['penilaian_id', 'kuisioner_id'], 'unique_jawaban_kuisioner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_kuisioner');
    }
};