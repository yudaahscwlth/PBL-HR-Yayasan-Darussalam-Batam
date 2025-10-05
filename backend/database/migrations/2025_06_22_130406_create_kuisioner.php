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
        Schema::create('kuisioner', function (Blueprint $table) {
            $table->id(); // BIGINT PRIMARY KEY AUTO_INCREMENT
            
            $table->string('kategori')
                  ->comment('Contoh: Kedisiplinan');
                  
            $table->text('pertanyaan')
                  ->comment('Isi pertanyaan');
                  
            $table->decimal('bobot', 3, 2)
                  ->default(1.0)
                  ->comment('Bobot pertanyaan, default: 1.0');
                  
            $table->boolean('aktif')
                  ->default(true)
                  ->comment('Status aktif pertanyaan (true/false)');
            
            $table->timestamps(); // created_at dan updated_at
            
            // Indexes untuk performa query
            $table->index('kategori');
            $table->index('aktif');
            $table->index(['kategori', 'aktif']); // Composite index untuk filter kategori + aktif
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuisioner');
    }
};