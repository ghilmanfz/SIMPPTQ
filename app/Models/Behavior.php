<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Behavior extends Model
{
    protected $fillable = [
        'santri_id',
        'date',
        'type',
        'category',
        'points',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'points' => 'integer',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
