<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LokasiPresensi extends Model
{
    protected $table = 'lokasi_presensis';

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    public function presensi(): HasMany
    {
        return $this->hasMany(PresensiPersonil::class);
    }
}
