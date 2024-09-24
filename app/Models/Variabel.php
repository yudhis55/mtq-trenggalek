<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variabel extends Model
{
    use HasFactory;

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);

    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }
}
