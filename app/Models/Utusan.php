<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Utusan extends Model
{
    use HasFactory;

    public function peserta(): HasMany
    {
        return $this->hasMany(Peserta::class);
    }
}
