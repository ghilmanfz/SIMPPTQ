<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function personil(): HasOne
    {
        return $this->hasOne(Personil::class);
    }

    /**
     * Cek apakah user memiliki sebuah permission lewat role-nya.
     * Super Admin selalu memiliki akses penuh agar tidak terkunci dari sistem.
     */
    public function hasPermissionTo(string $key): bool
    {
        if ($this->role === null) {
            return false;
        }

        if ($this->role->name === 'superadmin') {
            return true;
        }

        return $this->role->hasPermission($key);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role !== null && $this->role->name === 'superadmin';
    }

    /**
     * Fungsi kerja pengajar (relevan untuk jadwal & tukar jam).
     */
    public function isPengajar(): bool
    {
        return $this->personil !== null
            && in_array($this->personil->fungsi_kerja, ['Pengajar', 'Dua Fungsi'], true);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));

        return strtoupper(mb_substr(($parts[0][0] ?? '') . ($parts[1][0] ?? ''), 0, 2));
    }
}
