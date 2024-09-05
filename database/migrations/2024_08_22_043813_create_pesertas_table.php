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
        Schema::create('pesertas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis_kelamin');
            $table->string('nik');
            $table->string('tempat_lahir');
            $table->date('tgl_lahir');
            $table->string('alamat_ktp');
            $table->string('alamat_domisili');
            $table->foreignId('utusan_id')->constrained(
                table: 'utusans',
                indexName: 'pesertas_utusan_id'
            )->cascadeOnDelete();
            $table->foreignId('cabang_id')->constrained(
                table: 'cabangs',
                indexName: 'pesertas_cabang_id'
            )->cascadeOnDelete();
            $table->string('kk_ktp');
            // $table->string('akta');
            // $table->string('ijazah');
            // $table->string('piagam');
            $table->string('pasfoto');
            $table->boolean('is_verified')->default(false);
            $table->foreignId('user_id')->constrained(
                table: 'users',
                indexName: 'pesertas_user_id'
            );
            $table->foreignId('tahun_id')->constrained(
                table: 'tahuns',
                indexName: 'pesertas_tahun_id'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesertas');
    }
};
