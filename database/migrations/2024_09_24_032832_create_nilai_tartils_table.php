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
        Schema::create('nilai_tartils', function (Blueprint $table) {
            $table->id();
            $table->decimal('tajwid', 2, 2)->nullable();
            $table->decimal('max_tajwid', 2, 2)->nullable();
            $table->decimal('irama_dan_suara', 2, 2)->nullable();
            $table->decimal('max_irama_dan_tajwid', 2, 2)->nullable();
            $table->decimal('fashahah', 2, 2)->nullable();
            $table->decimal('max_fashahah', 2, 2)->nullable();
            $table->decimal('total', 4, 2)->nullable();
            $table->foreignId('peserta_id')->constrained(
                table: 'pesertas',
                indexName: 'tartil_peserta_id'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_tartils');
    }
};
