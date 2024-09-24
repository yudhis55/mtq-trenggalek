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
        Schema::create('nilai_lima_juzs', function (Blueprint $table) {
            $table->id();
            $table->decimal('til_tajwid', 2, 2)->nullable();
            $table->decimal('max_til_tajwid', 2, 2)->nullable();
            $table->decimal('til_lagu', 2, 2)->nullable();
            $table->decimal('max_til_lagu', 2, 2)->nullable();
            $table->decimal('til_suara', 2, 2)->nullable();
            $table->decimal('max_til_suara', 2, 2)->nullable();
            $table->decimal('til_fashahah', 2, 2)->nullable();
            $table->decimal('max_til_fashahah', 2, 2)->nullable();
            $table->decimal('tah_tahfizh', 2, 2)->nullable();
            $table->decimal('max_tah_tahfizh', 2, 2)->nullable();
            $table->decimal('tah_tajwid', 2, 2)->nullable();
            $table->decimal('max_tah_tajwid', 2, 2)->nullable();
            $table->decimal('tah_fashahah', 2, 2)->nullable();
            $table->decimal('max_tah_fashahah', 2, 2)->nullable();
            $table->decimal('total_tilawah', 4, 2)->nullable();
            $table->decimal('total_tahfizh', 4, 2)->nullable();
            $table->decimal('total', 4, 2)->nullable();
            $table->foreignId('peserta_id')->constrained(
                table: 'pesertas',
                indexName: 'lima_juz_peserta_id'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_lima_juzs');
    }
};
