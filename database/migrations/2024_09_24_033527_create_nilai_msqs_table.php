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
        Schema::create('nilai_msqs', function (Blueprint $table) {
            $table->id();
            $table->decimal('terjemahan_dan_penghayatan', 2, 2)->nullable();
            $table->decimal('max_terjemahan_dan_penghayatan', 2, 2)->nullable();
            $table->decimal('penghayatan_dan_retorika', 2, 2)->nullable();
            $table->decimal('max_penghayatan_dan_retorika', 2, 2)->nullable();
            $table->decimal('tilawah', 2, 2)->nullable();
            $table->decimal('max_tilawah', 2, 2)->nullable();
            $table->decimal('total', 4, 2)->nullable();
            $table->foreignId('peserta_id')->constrained(
                table: 'pesertas',
                indexName: 'msq_peserta_id'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_msqs');
    }
};
