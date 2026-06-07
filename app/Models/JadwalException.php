<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalException extends Model
{
    protected $fillable = [
        'jadwal_id',
        'date',
        'type',
        'substitute_personil_id',
        'swap_request_id',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function substitute(): BelongsTo
    {
        return $this->belongsTo(Personil::class, 'substitute_personil_id');
    }

    public function swapRequest(): BelongsTo
    {
        return $this->belongsTo(SwapRequest::class);
    }
}
