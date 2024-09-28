<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class NilaiLimaJuz extends Model
{
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class);
    }
    use HasFactory;

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class);
    }
}
