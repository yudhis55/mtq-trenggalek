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
        Schema::create('nilai_mushafs', function (Blueprint $table) {
            $table->id();
            $table->decimal('kebenaran_kaidah_khat', 2, 2)->nullable();
            $table->decimal('max_kebenaran_kaidah_khat', 2, 2)->nullable();
            $table->decimal('keindahan_khat', 2, 2)->nullable();
            $table->decimal('max_keindahan_khat', 2, 2)->nullable();
            $table->decimal('keindahan_hiasan_dan_lukisan', 2, 2)->nullable();
            $table->decimal('max_keindahan_hiasan_dan_lukisan', 2, 2)->nullable();
            $table->decimal('total', 4, 2)->nullable();
            $table->foreignId('peserta_id')->constrained(
                table: 'pesertas',
                indexName: 'mushaf_peserta_id'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_mushafs');
    }
};
