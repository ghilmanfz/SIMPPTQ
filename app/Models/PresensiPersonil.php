<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiPersonil extends Model
{
    protected $table = 'presensi_personils';

    protected $fillable = [
        'personil_id',
        'lokasi_presensi_id',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_lat',
        'check_in_lng',
        'check_out_lat',
        'check_out_lng',
        'status',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function personil(): BelongsTo
    {
        return $this->belongsTo(Personil::class);
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(LokasiPresensi::class, 'lokasi_presensi_id');
    }
}
