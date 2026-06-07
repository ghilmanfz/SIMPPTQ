<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mapel extends Model
{
    protected $table = 'mapels';

    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
    ];

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
