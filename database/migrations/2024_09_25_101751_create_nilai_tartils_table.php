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
            $table->decimal('tajwid')->nullable();
            $table->decimal('irama_dan_suara')->nullable();
            $table->decimal('fashahah')->nullable();
            $table->decimal('total')->nullable();
            $table->foreignId('peserta_id')->constrained(
                table: 'pesertas',
                indexName: 'tartils_peserta_id'
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
