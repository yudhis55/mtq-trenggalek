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
        Schema::create('nilai_dekorasis', function (Blueprint $table) {
            $table->id();
            $table->decimal('kebenaran_kaidah_khath', 2, 2)->nullable();
            $table->decimal('max_kebenaran_kaidah_khath', 2, 2)->nullable();
            $table->decimal('unsur_seni_rupa', 2, 2)->nullable();
            $table->decimal('max_unsur_seni_rupa', 2, 2)->nullable();
            $table->decimal('sentuhan_akhir', 2, 2)->nullable();
            $table->decimal('max_sentuhan_akhir', 2, 2)->nullable();
            $table->decimal('total', 4, 2)->nullable();
            $table->foreignId('peserta_id')->constrained(
                table: 'pesertas',
                indexName: 'mkq_peserta_id'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_dekorasis');
    }
};
