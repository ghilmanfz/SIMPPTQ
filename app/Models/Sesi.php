<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sesi extends Model
{
    protected $table = 'sesis';

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'order',
    ];

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }
}
