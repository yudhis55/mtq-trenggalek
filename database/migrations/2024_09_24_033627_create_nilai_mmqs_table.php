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
        Schema::create('nilai_mmqs', function (Blueprint $table) {
            $table->id();
            $table->decimal('bobot_materi', 2, 2)->nullable();
            $table->decimal('max_bobot_materi', 2, 2)->nullable();
            $table->decimal('kaidah_dan_gaya_bahasa', 2, 2)->nullable();
            $table->decimal('max_kaidah_dan_gaya_bahasa', 2, 2)->nullable();
            $table->decimal('logika_dan_organisasi_pesan', 2, 2)->nullable();
            $table->decimal('max_logika_dan_organisasi_pesan', 2, 2)->nullable();
            $table->decimal('presentasi', 2, 2)->nullable();
            $table->decimal('max_presentasi', 2, 2)->nullable();
            $table->decimal('total', 4, 2)->nullable();
            $table->foreignId('peserta_id')->constrained(
                table: 'pesertas',
                indexName: 'mmq_peserta_id'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_mmqs');
    }
};
