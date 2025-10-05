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
        Schema::create('periode_kuisioner', function (Blueprint $table) {
            $table->id(); // BIGINT PRIMARY KEY AUTO_INCREMENT
            
            // Foreign Keys
            $table->foreignId('periode_id')
                  ->constrained('periode_penilaian')
                  ->onDelete('cascade')
                  ->comment('FK ke periode_penilaian.id');
                  
            $table->foreignId('kuisioner_id')
                  ->constrained('kuisioner')
                  ->onDelete('cascade')
                  ->comment('FK ke kuisioner.id');
            
            $table->timestamps(); // created_at dan updated_at
            
            // Indexes untuk performa query
            $table->index('periode_id');
            $table->index('kuisioner_id');
            
            // Unique constraint untuk mencegah duplikasi
            // Satu kuisioner hanya bisa digunakan sekali per periode
            $table->unique(['periode_id', 'kuisioner_id'], 'unique_periode_kuisioner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_kuisioner');
    }
};