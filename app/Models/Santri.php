<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Santri extends Model
{
    protected $table = 'santris';

    protected $fillable = [
        'name',
        'nis',
        'nisn',
        'birth_place',
        'birth_date',
        'gender',
        'kelas_id',
        'photo_path',
        'status',
        'address',
        'wali_name',
        'wali_phone',
        'wali_relation',
        'card_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function presences(): HasMany
    {
        return $this->hasMany(SantriPresence::class);
    }

    public function behaviors(): HasMany
    {
        return $this->hasMany(Behavior::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function classHistories(): HasMany
    {
        return $this->hasMany(ClassHistory::class)->latest();
    }

    public function isActive(): bool
    {
        return $this->status === 'Aktif';
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    /**
     * Buat token kartu unik baru (dipakai saat generate / cetak ulang kartu).
     */
    public static function generateCardToken(): string
    {
        return 'STQ-' . strtoupper(Str::random(10));
    }
}
