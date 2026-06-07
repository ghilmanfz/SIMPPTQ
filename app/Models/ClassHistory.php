<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassHistory extends Model
{
    protected $table = 'class_histories';

    protected $fillable = [
        'santri_id',
        'kelas_id',
        'tahun_ajaran_id',
        'action',
        'from_kelas',
        'to_kelas',
        'note',
        'created_by',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Catat satu peristiwa perubahan kelas untuk seorang santri.
     * Dipanggil dari mana pun kelas_id santri berubah agar perpindahan terekam.
     */
    public static function record(Santri $santri, string $action, ?Kelas $from, ?Kelas $to, ?string $note = null): self
    {
        return self::create([
            'santri_id' => $santri->id,
            'kelas_id' => $to?->id,
            'tahun_ajaran_id' => $to?->tahun_ajaran_id ?? $from?->tahun_ajaran_id,
            'action' => $action,
            'from_kelas' => $from?->name,
            'to_kelas' => $to?->name,
            'note' => $note,
            'created_by' => auth()->id(),
        ]);
    }
}
