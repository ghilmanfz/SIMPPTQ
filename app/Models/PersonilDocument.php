<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonilDocument extends Model
{
    protected $fillable = [
        'personil_id',
        'name',
        'file_path',
        'mime',
        'size',
    ];

    public function personil(): BelongsTo
    {
        return $this->belongsTo(Personil::class);
    }
}
