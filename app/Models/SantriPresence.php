<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SantriPresence extends Model
{
    protected $table = 'santri_presences';

    protected $fillable = [
        'santri_id',
        'kelas_id',
        'date',
        'time',
        'kegiatan',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
