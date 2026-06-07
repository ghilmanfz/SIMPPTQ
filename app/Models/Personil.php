<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personil extends Model
{
    protected $table = 'personils';

    protected $fillable = [
        'user_id',
        'name',
        'nik',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'phone',
        'email',
        'jabatan',
        'unit_kerja',
        'status_kerja',
        'fungsi_kerja',
        'salary_base',
        'salary_allowance',
        'salary_deduction',
        'photo_path',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'salary_base' => 'decimal:2',
        'salary_allowance' => 'decimal:2',
        'salary_deduction' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PersonilDocument::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(PresensiPersonil::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function swapRequests(): HasMany
    {
        return $this->hasMany(SwapRequest::class, 'requester_personil_id');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function isPengajar(): bool
    {
        return in_array($this->fungsi_kerja, ['Pengajar', 'Dua Fungsi'], true);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}
