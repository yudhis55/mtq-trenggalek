<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tahun extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saving(function ($model) {
            // Jika tahun yang diaktifkan
            if ($model->is_active) {
                // Set semua tahun lain menjadi tidak aktif
                static::where('is_active', true)
                    ->where('id', '!=', $model->id)
                    ->update(['is_active' => false]);
            }
        });
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(Peserta::class);
    }
}
