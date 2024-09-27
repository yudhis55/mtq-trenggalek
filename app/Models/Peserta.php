<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Peserta extends Model
{
    use HasFactory;

    protected $casts = [
        'tgl_lahir' => 'date',
    ];

    public function getTempatDanTanggalLahirAttribute()
    {
        return $this->tempat_lahir . ', ' . $this->tgl_lahir->format('d F Y');
    }

    public function utusan(): BelongsTo
    {
        return $this->belongsTo(Utusan::class);
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tahun(): BelongsTo
    {
        return $this->belongsTo(Tahun::class);
    }

    public function nilaitartil(): HasOne
    {
        return $this->hasOne(NilaiTartil::class);
    }
}
