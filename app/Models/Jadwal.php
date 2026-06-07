<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jadwal extends Model
{
    protected $table = 'jadwals';

    protected $fillable = [
        'tahun_ajaran_id',
        'personil_id',
        'kelas_id',
        'mapel_id',
        'sesi_id',
        'day',
        'start_time',
        'end_time',
    ];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function personil(): BelongsTo
    {
        return $this->belongsTo(Personil::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(Sesi::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(JadwalException::class);
    }

    public function swapRequests(): HasMany
    {
        return $this->hasMany(SwapRequest::class);
    }
}
