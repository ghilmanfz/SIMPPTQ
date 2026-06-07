<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwapRequest extends Model
{
    protected $fillable = [
        'jadwal_id',
        'requester_personil_id',
        'target_personil_id',
        'date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Personil::class, 'requester_personil_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Personil::class, 'target_personil_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
